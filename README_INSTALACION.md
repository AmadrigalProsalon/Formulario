# Sistema de Formularios RH - Proyecto completo

Este paquete contiene el código fuente del sistema RH con:

- Formularios dinámicos.
- Panel admin.
- Usuarios administradores.
- Catálogos.
- Respuestas.
- Permisos y ausencias.
- Solicitud pública con buscador de empleado.
- Generación de DOCX para permisos.
- Firma digital desactivada por configuración.
- Flujo de firma física.
- Envío de documento a colaborador, líder y RH.
- RH marca formato recibido, pendiente, observaciones o cancelado.
- Vacaciones descuentan solo cuando RH marca formato recibido.
- Subida de formato firmado escaneado.
- Calendario de ausencias.
- Importación masiva de empleados desde CSV/XLSX.
- Docker con Nginx, PHP-FPM, MySQL y phpMyAdmin.

## Archivos no incluidos

No incluye `vendor`, `node_modules`, `.env` ni respaldos de base de datos.

## Instalación rápida en VPS

```bash
cd ~/Formulario
cp .env.example .env
nano .env

docker compose up -d --build

docker exec -it formulario_rh_app php artisan key:generate --force
docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan db:seed --class=AdminUserSeeder --force
docker exec -it formulario_rh_app php artisan storage:link
docker exec -it formulario_rh_app php artisan optimize:clear
```

Si Nginx no encuentra assets:

```bash
docker exec -it formulario_rh_app npm run build
rm -rf public/build
docker cp formulario_rh_app:/var/www/html/public/build ./public/build
docker compose restart nginx
```

## URLs

- Sistema: `http://IP_DEL_VPS:8092`
- Login: `http://IP_DEL_VPS:8092/login`
- phpMyAdmin: `http://IP_DEL_VPS:8093`
- Solicitud pública: `http://IP_DEL_VPS:8092/permisos/solicitud`
- Permisos admin: `http://IP_DEL_VPS:8092/admin/permisos`
- Calendario: `http://IP_DEL_VPS:8092/admin/permisos/calendario`
- Empleados: `http://IP_DEL_VPS:8092/admin/permisos-catalogos/empleados`

## Variables importantes

```env
RH_ADMIN_EMAIL=amadrigal@prosalon.mx
RH_ADMIN_PASSWORD="Cambiar123456!"
PERMISOS_RH_EMAIL=rh@prosalon.mx
PERMISOS_FIRMA_DIGITAL=false
```

## Importación de empleados

Ruta: `/admin/permisos-catalogos/empleados/importar`

Columnas sugeridas:

```text
numero_empleado, nombre, correo, area, puesto, lider, correo_lider, fecha_ingreso, activo
```

## Flujo de permisos

1. Colaborador busca su nombre y llena solicitud.
2. Sistema genera documento DOCX.
3. Se envía al líder, colaborador y RH.
4. Líder y colaborador firman físicamente.
5. RH sube el formato firmado escaneado.
6. RH marca recibido, pendiente, observaciones o cancelado.
7. Vacaciones solo se descuentan cuando queda como recibido.
