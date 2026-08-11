# Instalación sin Vite, npm ni compilación de diseño

Esta versión conserva Laravel y todas las pantallas Blade, pero el diseño se carga directamente con Tailwind CSS por CDN.

No se requiere ejecutar:

- `npm install`
- `npm run build`
- `vite`
- generar `public/build/manifest.json`

## Primera instalación local

```powershell
Copy-Item .env.example .env
docker compose down -v
docker compose up -d --build
```

Generar APP_KEY sin depender de que `.env` exista dentro de la imagen:

```powershell
$KEY = docker compose exec -T app php -r "echo 'base64:'.base64_encode(random_bytes(32));"
(Get-Content .env) -replace '^APP_KEY=.*$', "APP_KEY=$KEY" | Set-Content .env
docker compose up -d --force-recreate app
```

Después:

```powershell
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
```

Abrir: `http://localhost:8092`

## Nota

El navegador necesita acceso a Internet para descargar Tailwind CSS y la fuente Inter desde CDN. El backend y la base de datos siguen funcionando dentro de Docker.
