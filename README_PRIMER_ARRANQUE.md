# Formulario RH — primer arranque

## Windows
Abra PowerShell en esta carpeta y ejecute:

```powershell
Set-ExecutionPolicy -Scope Process Bypass
.\INSTALAR.ps1
```

El instalador crea `.env`, genera una APP_KEY segura, recrea MySQL, construye los contenedores, ejecuta migraciones y carga datos iniciales.

- Sistema: http://localhost:8092
- Usuario: `amadrigal@prosalon.mx`
- Contraseña: `Admin123*`
- phpMyAdmin: http://localhost:8093

> El instalador usa `docker compose down -v`; elimina la base local anterior para garantizar una instalación limpia.
