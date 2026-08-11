$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

function Write-Utf8NoBom {
    param(
        [Parameter(Mandatory = $true)][string]$Path,
        [Parameter(Mandatory = $true)][string]$Content
    )

    $encoding = New-Object System.Text.UTF8Encoding($false)
    [System.IO.File]::WriteAllText((Resolve-Path $Path), $Content, $encoding)
}

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    throw "Docker no está instalado o no está disponible en PATH. Abre Docker Desktop y vuelve a ejecutar el instalador."
}

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
}

$envText = Get-Content ".env" -Raw

if ($envText -notmatch '(?m)^APP_KEY=base64:.+$') {
    # Compatible con Windows PowerShell 5.1 y PowerShell 7.
    $bytes = New-Object byte[] 32
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    try {
        $rng.GetBytes($bytes)
    }
    finally {
        $rng.Dispose()
    }

    $key = "base64:" + [Convert]::ToBase64String($bytes)

    if ($envText -match '(?m)^APP_KEY=.*$') {
        $envText = [regex]::Replace($envText, '(?m)^APP_KEY=.*$', "APP_KEY=$key")
    }
    else {
        $envText = "APP_KEY=$key`r`n" + $envText
    }

    Write-Utf8NoBom -Path ".env" -Content $envText
    Write-Host "APP_KEY generada correctamente." -ForegroundColor Green
}
else {
    Write-Host "La APP_KEY ya existe; se conservará." -ForegroundColor Yellow
}

Write-Host "Eliminando instalación local anterior..." -ForegroundColor Cyan
docker compose down -v
if ($LASTEXITCODE -ne 0) {
    throw "No se pudo detener la instalación anterior. Verifica que Docker Desktop esté iniciado."
}

Write-Host "Construyendo y levantando los contenedores..." -ForegroundColor Cyan
docker compose up -d --build
if ($LASTEXITCODE -ne 0) {
    throw "Docker Compose no pudo construir o iniciar el proyecto."
}

Write-Host "Esperando a que Laravel complete migraciones y datos iniciales..." -ForegroundColor Cyan
$ready = $false
for ($i = 1; $i -le 60; $i++) {
    docker compose exec -T app php artisan migrate:status *> $null
    if ($LASTEXITCODE -eq 0) {
        $ready = $true
        break
    }
    Start-Sleep -Seconds 3
}

Write-Host ""
if ($ready) {
    Write-Host "Sistema listo en http://localhost:8092" -ForegroundColor Green
}
else {
    Write-Host "Los contenedores iniciaron, pero Laravel aún está terminando la instalación." -ForegroundColor Yellow
    Write-Host "Revisa el avance con: docker compose logs -f app" -ForegroundColor Yellow
}

Write-Host "Usuario: amadrigal@prosalon.mx"
Write-Host "Contraseña: Admin123*"
Write-Host "phpMyAdmin: http://localhost:8093"
