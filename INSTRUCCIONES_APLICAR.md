# Parche perfiles de puesto + autollenado de requisición

Este parche deja listos los archivos para Git y agrega:

- Parser DOCX más tolerante para perfiles de puesto.
- Importación de objetivo, requerimientos, cualidades, habilidades y responsabilidades.
- Panel admin `Perfiles de Puesto`.
- API de búsqueda de perfiles.
- Autollenado en `Requisición de Personal` al seleccionar un perfil importado.

## Aplicación

Copia las carpetas del ZIP encima del proyecto Laravel.

Luego ejecuta:

```bash
cd ~/Formulario
python3 scripts/instalar_perfiles_puesto_patch.py

docker compose up -d --build

docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan db:seed --class=PerfilPuestoRequisicionAutofillSeeder --force
docker exec -it formulario_rh_app php artisan optimize:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear
```

## Probar

1. Entra a `/admin/perfiles-puesto`.
2. Sube el Word `ATENCION AL CLIENTE.docx`.
3. Revisa que detecte objetivo, requerimientos, habilidades y responsabilidades.
4. Entra a `/f/requisicion-personal`.
5. Busca `Atención al Cliente` en `Perfil de puesto base`.
6. Al seleccionarlo, debe autollenar campos de la requisición.

## Qué hace cargar un DOCX

Al subir un Word, el sistema no crea una requisición todavía. Lo que hace es crear/actualizar un registro en `perfiles_puesto`. Ese perfil queda como base para que, cuando alguien llene la requisición, pueda seleccionar el perfil y autollenar los campos.
