# Importar empleados y vacaciones desde CSV/Excel

Este proyecto ya incluye el importador para el archivo de saldos de vacaciones.

## Ruta en el panel

Entra como admin y abre:

```text
/admin/permisos-catalogos/empleados/importar
```

También aparece en el menú como:

```text
Permisos y ausencias > Importar vacaciones CSV/Excel
```

## Archivo incluido

Se dejó el archivo recibido en:

```text
database/imports/saldo_vacaciones_real.csv
```

Puedes subir ese archivo desde la pantalla de importación.

## Reglas de importación

- `CLAVE` se usa como `numero_empleado`.
- `NOMBRE` se usa como nombre del empleado.
- `DEPARTAMENTO` crea o asigna el área.
- `PUESTO` actualiza el puesto.
- `JEFE DIRECTO` crea o asigna líder.
- `FECHA INGRESO` actualiza fecha de ingreso.
- `PROPORCIONALES` se toma como saldo oficial disponible.
- Las columnas sin encabezado con fechas se guardan como vacaciones históricas tomadas.

## Importante

Las fechas históricas se guardan en `permisos_solicitudes` con estatus `formato_recibido`, para que aparezcan en el calendario.

Al reimportar el mismo archivo, el sistema reemplaza únicamente las vacaciones históricas importadas por este importador para cada empleado. No borra solicitudes hechas manualmente desde el sistema.

## Después de copiar cambios al VPS

```bash
docker compose up -d --build app nginx

docker exec -it formulario_rh_app composer dump-autoload
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear

docker compose restart app nginx
```

No es necesario tocar `.env` ni cambiar la base de datos si el proyecto ya está funcionando.
