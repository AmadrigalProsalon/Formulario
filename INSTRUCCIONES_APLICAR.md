# Parche Perfiles de Puesto + Requisición de Personal

Este ZIP incluye el módulo para importar perfiles de puesto desde Word/DOCX y usarlos para autollenar el formulario de Requisición de Personal.

## Qué corrige esta versión

- Migración segura para `perfiles_puesto` aunque la tabla ya exista con estructura anterior.
- Ya no falla si falta la columna `responsabilidades`.
- Parser DOCX más tolerante para detectar:
  - objetivo del puesto;
  - requerimientos mínimos;
  - cualidades;
  - habilidades;
  - responsabilidades;
  - escolaridad;
  - experiencia;
  - nivel de inglés;
  - software detectado.
- Buscador de perfil de puesto en el formulario `requisicion-personal`.
- Autollenado de campos de requisición con base en el perfil importado.
- Se incluye alias del seeder `RequisicionPersonalConPerfilesSeeder` para que funcionen comandos anteriores.

## Cómo aplicar

1. Copia todas las carpetas encima de tu proyecto Laravel.

2. Ejecuta el instalador:

```bash
cd ~/Formulario
python3 scripts/instalar_perfiles_puesto_patch.py
```

3. Reconstruye Docker:

```bash
docker compose up -d --build
```

4. Espera a MySQL:

```bash
docker exec formulario_rh_db sh -c 'until mysqladmin ping -h 127.0.0.1 -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --silent; do echo "Esperando MySQL..."; sleep 3; done; echo "MySQL listo"'
```

5. Ejecuta migraciones y seeder:

```bash
docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan db:seed --class=PerfilPuestoRequisicionAutofillSeeder --force
```

También funciona este comando viejo:

```bash
docker exec -it formulario_rh_app php artisan db:seed --class=RequisicionPersonalConPerfilesSeeder --force
```

6. Limpia cachés:

```bash
docker exec -it formulario_rh_app php artisan optimize:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear
```

## Rutas

Admin para importar perfiles:

```text
/admin/perfiles-puesto
```

Formulario de requisición:

```text
/f/requisicion-personal
```

## Prueba

1. Entra a `/admin/perfiles-puesto`.
2. Sube el DOCX de Atención al Cliente.
3. Revisa que se llenen objetivo, requerimientos, habilidades y responsabilidades.
4. Entra a `/f/requisicion-personal`.
5. Busca el perfil importado en `Perfil de puesto base`.
6. Selecciónalo y verifica el autollenado.
