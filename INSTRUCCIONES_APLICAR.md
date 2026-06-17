# Parche UX Admin RH: calendario real + menú agrupado

## Cambios incluidos

- Calendario mensual real para ausencias/permisos.
- Filtros por mes, año, área, tipo de permiso y estado.
- Menú admin reorganizado por grupos:
  - Principal
  - Formularios
  - Permisos y ausencias
  - Vacantes
  - Sistema
- Botón de cerrar sesión fijo al final del sidebar, sin expandirse cuando la página es grande.
- Ruta nueva: `/admin/permisos/calendario`.

## Aplicación

Copiar las carpetas del ZIP encima del proyecto y ejecutar:

```bash
cd ~/Formulario
python3 scripts/instalar_admin_ux_patch.py

docker compose up -d --build

docker exec -it formulario_rh_app php artisan optimize:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear
```

## Probar

```text
http://31.97.215.46:8092/admin/permisos/calendario
```

## Nota

La vista usa las tablas existentes:

- `permisos_solicitudes`
- `empleados`
- `areas`
- `tipos_permisos`

Si todavía no hay solicitudes, el calendario se mostrará vacío.
