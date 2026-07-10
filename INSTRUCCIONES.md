# Parche importador de vacaciones con líderes desde JEFE DIRECTO

Este parche corrige la importación del archivo de vacaciones cuando el CSV trae la columna **JEFE DIRECTO**.

## Qué corrige

- Los valores de **JEFE DIRECTO** se tratan como líderes.
- Si el líder ya existe como empleado, se marca `es_lider = 1`.
- Si el líder no existe, se crea automáticamente con una clave tipo `LIDER-XXXXXXXXXX`.
- Evita el error: `Field 'numero_empleado' doesn't have a default value`.
- Si un empleado ya había sido marcado como líder y luego aparece como empleado normal en otra fila, conserva `es_lider = 1`.
- Las columnas sin encabezado con fechas se siguen importando como vacaciones históricas tomadas.
- El saldo oficial se toma de `PROPORCIONALES` y no se descuenta doble.

## Instalación

Desde el VPS:

```bash
cd ~/Formulario

# Respaldar controlador actual
cp app/Http/Controllers/Admin/PermisosEmpleadosController.php app/Http/Controllers/Admin/PermisosEmpleadosController.php.bak_$(date +%Y%m%d_%H%M%S)

# Descomprimir el parche encima del proyecto
unzip parche_lideres_vacaciones_csv.zip -d .

# Reconstruir y limpiar caches
docker compose up -d --build app nginx

docker exec -it formulario_rh_app composer dump-autoload
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear

docker compose restart app nginx
```

## Importar

Entra a:

```text
http://31.97.215.46:8092/admin/permisos-catalogos/empleados/importar
```

Sube el archivo CSV/Excel de vacaciones.

## Verificar líderes

```bash
docker exec -i formulario_rh_db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' <<'SQL'
SELECT id, numero_empleado, nombre, es_lider, activo
FROM empleados
WHERE es_lider = 1
ORDER BY nombre;
SQL
```

## Archivo incluido

También se incluye una copia del CSV real en:

```text
database/imports/saldo_vacaciones_real.csv
```

## SQL opcional

Se incluye:

```text
database/sql/fix_lideres_empleados.sql
```

Úsalo solo si necesitas reparar líderes ya existentes sin `numero_empleado`.
