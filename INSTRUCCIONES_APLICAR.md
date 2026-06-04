# Parche integrado — Permisos, Ausencias, Firmas y Documentos RH

Este ZIP ya viene integrado para que no tengas que editar rutas ni pegar código manualmente.

Incluye:

- Módulo general de Permisos y Ausencias.
- Vacaciones, permisos con goce, permisos sin goce, permiso médico y otros.
- Áreas, empleados, líderes y tipos de permiso.
- Firma digital interna con token.
- Validación para no permitir solicitudes cruzadas en las mismas fechas para el mismo empleado.
- Validación de saldo solo cuando el tipo de permiso requiere saldo, como vacaciones.
- Generación automática de DOCX inicial.
- Envío del DOCX inicial al colaborador, líder y RH.
- Generación automática de DOCX firmado cuando firma colaborador y líder.
- Envío automático del DOCX firmado a RH.
- Descarga y reenvío de documentos desde el panel RH.
- `routes/web.php` completo ya con los requires necesarios.
- `routes/permisos.php` y `routes/permisos_documentos.php` incluidos.
- Plantilla Word incluida en `resources/templates/formato_permiso.docx`.

## Cómo aplicar

1. Descomprime el ZIP.
2. Copia las carpetas encima del proyecto Laravel.
3. No necesitas agregar manualmente `require __DIR__ . '/permisos.php';` ni `require __DIR__ . '/permisos_documentos.php';` porque el `routes/web.php` incluido ya los trae.
4. Ejecuta:

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

## Correo RH

El sistema busca el correo RH en este orden:

1. `PERMISOS_RH_EMAIL`
2. `RH_FORM_MAIL_TO`
3. `rh@prosalon.mx` como valor de respaldo

Si ya tienes `RH_FORM_MAIL_TO` en `.env`, no necesitas agregar nada.

Si quieres cambiarlo, agrega o ajusta:

```env
PERMISOS_RH_EMAIL=rh@prosalon.mx
```

## Plantilla DOCX

El sistema ya incluye la plantilla en:

```text
resources/templates/formato_permiso.docx
```

Si quieres usar otra, puedes configurar:

```env
PERMISOS_TEMPLATE_PATH=/var/www/html/resources/templates/formato_permiso.docx
```

Pero no es obligatorio.

## URLs

Solicitud pública:

```text
/permisos/solicitud
```

Panel RH:

```text
/admin/permisos
```

Empleados:

```text
/admin/permisos-catalogos/empleados
```

Áreas:

```text
/admin/permisos-catalogos/areas
```

Tipos de permiso:

```text
/admin/permisos-catalogos/tipos
```

Documentos por solicitud:

```text
/admin/permisos/{id}
```

Desde el detalle de la solicitud puedes descargar o reenviar:

- formato inicial;
- formato firmado;
- formato firmado a RH.

## Flujo esperado

1. RH registra áreas, líderes y empleados.
2. El colaborador entra a `/permisos/solicitud`.
3. Selecciona colaborador, tipo de permiso y fechas.
4. Si el permiso requiere saldo, el sistema valida días disponibles.
5. Si ya hay una solicitud activa cruzada para el mismo empleado, el sistema bloquea el envío.
6. Se crea la solicitud.
7. Se genera el formato DOCX inicial.
8. Se manda el formato al colaborador, al líder y a RH.
9. Colaborador firma con token.
10. Líder firma con token.
11. Cuando ambas firmas están completas, se genera el DOCX firmado.
12. El DOCX firmado se envía automáticamente a RH.
13. RH marca formato recibido, formato pendiente, con observaciones o cancelado.
