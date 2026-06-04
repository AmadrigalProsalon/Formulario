# Parche integrado: Permisos físicos, documentos y búsqueda de empleados

Este ZIP deja el módulo de permisos/ausencias con el flujo físico solicitado:

- La firma digital queda desactivada por configuración.
- El sistema genera el DOCX y lo manda por correo al líder, colaborador y RH.
- El líder descarga/firma físicamente, luego firma el colaborador y entregan a RH.
- RH marca: formato recibido, pendiente, observaciones o cancelado.
- Los días de vacaciones solo se descuentan cuando RH marca `formato_recibido`.
- Si RH cancela, no se descuentan días.
- Admin de empleados con filtros por departamento y búsqueda.
- Formulario público con buscador/autocomplete de empleado.

## Aplicación

Copiar el contenido de este ZIP encima del proyecto Laravel.

El archivo `routes/permisos.php` viene completo. Si tu `routes/web.php` todavía no lo carga, agrega una sola vez al final:

```php
require __DIR__ . '/permisos.php';
```

Variables recomendadas en `.env`:

```env
PERMISOS_FIRMA_DIGITAL=false
PERMISOS_RH_EMAIL=rh@prosalon.mx
PERMISOS_DOCUMENTOS_DISK=public
# Opcional; si no existe, usa resources/templates/formato_permiso.docx
PERMISOS_TEMPLATE_PATH=
```

Comandos:

```bash
cd ~/Formulario

docker compose up -d --build

docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan storage:link
docker exec -it formulario_rh_app php artisan optimize:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear

docker exec -it formulario_rh_app npm run build
rm -rf public/build
docker cp formulario_rh_app:/var/www/html/public/build ./public/build
docker compose restart nginx
```

URLs:

- Público: `/permisos/solicitud`
- Admin solicitudes: `/admin/permisos`
- Admin empleados: `/admin/permisos-catalogos/empleados`
