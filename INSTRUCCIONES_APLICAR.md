# Parche: perfiles de puesto por departamento + autollenado de requisición

Este ZIP agrega/actualiza el módulo de **Perfiles de Puesto** para que el formulario **Requisición de Personal** funcione así:

1. Seleccionas departamento.
2. El sistema muestra solo los perfiles importados de ese departamento.
3. Seleccionas el perfil/puesto.
4. Se autollenan los datos detectados desde el Word: puesto, área, puesto a quien reporta, funciones, objetivo, requerimientos, habilidades, experiencia, inglés y software.

También incluye el fix de migración para las columnas faltantes en `perfiles_puesto`, incluyendo `responsabilidades` y `texto_original`.

## Aplicación

Copia las carpetas del ZIP encima de tu proyecto Laravel.

Después ejecuta:

```bash
cd ~/Formulario
python3 scripts/instalar_perfiles_puesto_patch.py

docker compose up -d --build

docker exec formulario_rh_db sh -c 'until mysqladmin ping -h 127.0.0.1 -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --silent; do echo "Esperando MySQL..."; sleep 3; done; echo "MySQL listo"'

docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan db:seed --class=PerfilPuestoRequisicionAutofillSeeder --force

docker exec -it formulario_rh_app php artisan optimize:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear
```

## Probar importación del DOCX

Entra a:

```text
http://31.97.215.46:8092/admin/perfiles-puesto
```

Sube el Word de descriptivo de puesto, por ejemplo **ATENCION AL CLIENTE.docx**.

El sistema guardará la información en:

```text
perfiles_puesto
```

## Probar requisición con departamento

Entra a:

```text
http://31.97.215.46:8092/f/requisicion-personal
```

En el bloque superior **Perfil de puesto base**:

1. Selecciona el departamento.
2. Selecciona el perfil disponible.
3. Revisa los campos autollenados.
4. Ajusta lo necesario antes de enviar.

## Nuevas rutas API

```text
/api/perfiles-puesto/areas
/api/perfiles-puesto/por-departamento?departamento=ATENCIÓN%20AL%20CLIENTE
/api/perfiles-puesto/buscar?departamento=ATENCIÓN%20AL%20CLIENTE&q=cliente
/api/perfiles-puesto/{id}
```

## Archivos principales incluidos

```text
app/Http/Controllers/PerfilPuestoApiController.php
app/Http/Controllers/PerfilPuestoController.php
app/Models/PerfilPuesto.php
app/Services/PerfilPuestoDocxParser.php
database/migrations/2026_06_04_000001_create_perfiles_puesto_table.php
database/seeders/PerfilPuestoRequisicionAutofillSeeder.php
database/seeders/RequisicionPersonalConPerfilesSeeder.php
routes/perfiles_puesto.php
resources/views/vendor/perfiles/requisicion-autofill.blade.php
resources/views/admin/perfiles-puesto/index.blade.php
scripts/instalar_perfiles_puesto_patch.py
```
