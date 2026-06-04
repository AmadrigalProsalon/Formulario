# Parche: Perfiles de Puesto desde DOCX + Requisición de Personal

Este parche agrega un módulo para importar descriptivos de puesto en Word (.docx), revisar la información detectada y usarla para autollenar el formulario de Requisición de Personal.

## Qué incluye

- Tabla `perfiles_puesto`.
- Tabla `perfil_puesto_responsabilidades`.
- Tabla base `requisiciones_personal` para control futuro de vacantes.
- Importador de DOCX.
- Parser para extraer datos de descriptivos de puesto.
- Vista admin `/admin/perfiles-puesto`.
- API pública interna para buscar perfiles.
- Autocomplete en el formulario de Requisición de Personal.
- Seeder para crear/actualizar el formulario `requisicion-personal`.

## Aplicación

Desde la raíz del proyecto:

```bash
unzip perfiles_puesto_requisicion_patch.zip -d .
python3 scripts/instalar_perfiles_puesto.py

docker compose up -d --build

docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan db:seed --class=RequisicionPersonalConPerfilesSeeder --force
docker exec -it formulario_rh_app php artisan optimize:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear
```

Si los estilos/JS no cargan:

```bash
docker exec -it formulario_rh_app npm run build
rm -rf public/build
docker cp formulario_rh_app:/var/www/html/public/build ./public/build
docker compose restart nginx app
```

## Prueba con el DOCX

1. Entra a:

```text
http://31.97.215.46:8092/admin/perfiles-puesto
```

2. Sube `ATENCION AL CLIENTE.docx`.
3. Revisa el perfil importado y guárdalo.
4. Entra al formulario:

```text
http://31.97.215.46:8092/f/requisicion-personal
```

5. En el campo "Perfil de puesto base" busca `Atención al Cliente`.
6. Selecciónalo y revisa cómo autollena campos como área, puesto que reporta, funciones, requerimientos y habilidades.

## Nota

El parser funciona mejor si los Word tienen estructura similar al documento de ejemplo:

- Identificador de puesto.
- Descripción del puesto.
- Objetivo del puesto.
- Requerimientos mínimos.
- Cualidades.
- Habilidades.
- Responsabilidades y actividades.
