# Parche módulo Permisos y Ausencias RH

Este ZIP agrega un módulo general para:

- Vacaciones.
- Permiso con goce de sueldo.
- Permiso sin goce de sueldo.
- Permiso médico.
- Otros permisos.
- Control de áreas y empleados.
- Firma digital interna con enlace por token.
- Control RH: formato recibido, pendiente, observaciones o cancelado.

## 1. Copiar archivos

Copia todas las carpetas del ZIP encima de tu proyecto Laravel.

## 2. Registrar rutas

En `routes/web.php`, al final del archivo, antes o después de `require __DIR__ . '/auth.php';`, agrega:

```php
require __DIR__ . '/permisos.php';
```

## 3. Ejecutar comandos en Docker

```bash
cd ~/Formulario

docker compose up -d --build

docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan storage:link
docker exec -it formulario_rh_app php artisan optimize:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear
```

## 4. Regenerar assets si es necesario

```bash
docker exec -it formulario_rh_app npm run build
rm -rf public/build
docker cp formulario_rh_app:/var/www/html/public/build ./public/build
docker compose restart nginx
```

## 5. URLs

Público:

```text
/permisos/solicitud
```

Admin:

```text
/admin/permisos
/admin/permisos-catalogos/empleados
/admin/permisos-catalogos/areas
/admin/permisos-catalogos/tipos
```

## 6. Datos que RH debe cargar

Primero cargar:

1. Áreas.
2. Empleados.
3. Líderes.
4. Asignar cada empleado a un área y líder.
5. Revisar fecha de ingreso y saldo/ajustes de vacaciones.

## 7. Lógica de vacaciones

Solo los tipos que tengan:

- `descuenta_vacaciones = 1`
- `requiere_saldo = 1`

validan saldo y apartan días como pendientes.

Cuando RH marca `Formato recibido`, los días pasan de pendientes a usados.

Cuando RH cancela o marca pendiente desde recibido, el saldo se ajusta automáticamente.

## 8. Firma digital interna

El sistema genera links únicos para colaborador y líder.

Cada firma guarda:

- imagen de firma;
- fecha y hora;
- IP;
- navegador;
- token único.

Esto sirve como control interno administrativo.
