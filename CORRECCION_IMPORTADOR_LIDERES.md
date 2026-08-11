# Corrección del importador de empleados

Esta versión corrige la creación automática de líderes durante la importación.

- Los líderes creados desde la columna `Jefe Directo` reciben una clave técnica estable: `LIDER-XXXXXXXXXXXX`.
- `numero_empleado` queda nullable para permitir registros administrativos cuando no exista una clave real en el archivo.
- Los empleados normales continúan usando la clave de la columna `CLAVE`/`NUMERO_EMPLEADO`.

## Aplicar sobre una instalación existente

```powershell
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
```
