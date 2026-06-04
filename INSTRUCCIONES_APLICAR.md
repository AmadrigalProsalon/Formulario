# Parche: Documentos DOCX y envío a RH para Permisos / Ausencias

Este parche agrega generación de documento Word para las solicitudes de permisos / ausencias y envío automático a RH.

## Qué agrega

- Generación de DOCX inicial cuando se crea una solicitud.
- Envío del formato inicial al colaborador, líder y RH.
- Generación de DOCX firmado cuando ya firmaron colaborador y líder.
- Envío automático del documento firmado al correo RH.
- Rutas admin para descargar y reenviar documentos.
- Plantilla Word de ejemplo con placeholders.

## 1. Copiar archivos

Copia el contenido del ZIP encima del proyecto Laravel.

## 2. Agregar variables al `.env`

```env
PERMISOS_RH_EMAIL=rh@prosalon.mx
PERMISOS_TEMPLATE_PATH=/var/www/html/storage/app/templates/formato_permiso.docx
PERMISOS_DOCUMENTOS_DISK=public
```

Ajusta `PERMISOS_RH_EMAIL` al correo real de RH.

Si quieres usar la plantilla de ejemplo incluida, cópiala dentro del contenedor o en el proyecto como:

```text
storage/app/templates/formato_permiso.docx
```

## 3. Agregar rutas

En `routes/web.php`, al final del archivo agrega:

```php
require __DIR__ . '/permisos_documentos.php';
```

Debe quedar junto con los otros `require` de rutas.

## 4. Ejecutar comandos

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

## 5. Integrar envío del documento inicial

Busca el controlador donde se guarda la solicitud de permiso. Normalmente puede ser:

```text
app/Http/Controllers/Permisos/PermisoSolicitudController.php
```

Después de crear la solicitud, agrega:

```php
app(\App\Services\Permisos\PermisoDocumentoWorkflowService::class)
    ->enviarDocumentoInicial($solicitud);
```

Ejemplo:

```php
$solicitud = PermisoSolicitud::create($data);

app(\App\Services\Permisos\PermisoDocumentoWorkflowService::class)
    ->enviarDocumentoInicial($solicitud);
```

## 6. Integrar generación del documento firmado

Busca el controlador donde se guarda la firma digital. Después de guardar la firma, agrega:

```php
app(\App\Services\Permisos\PermisoDocumentoWorkflowService::class)
    ->procesarFirmasCompletas($solicitud);
```

Ejemplo:

```php
$firma->update([
    'estatus' => 'firmado',
    'firma_path' => $firmaPath,
    'firmado_at' => now(),
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);

app(\App\Services\Permisos\PermisoDocumentoWorkflowService::class)
    ->procesarFirmasCompletas($solicitud);
```

El servicio revisa si ya existen las dos firmas requeridas:

- colaborador
- lider

Cuando ambas están firmadas, genera el documento final y lo envía a RH.

## 7. Agregar botones en el panel RH

En la vista donde muestras cada solicitud de permisos, puedes incluir este parcial:

```blade
@include('admin.permisos._documentos_botones', ['solicitud' => $solicitud])
```

Eso mostrará botones para:

- Descargar formato inicial.
- Reenviar formato inicial.
- Descargar formato firmado.
- Enviar firmado a RH.

## 8. Placeholders disponibles en la plantilla Word

La plantilla DOCX puede usar estos placeholders:

```text
${folio}
${tipo_permiso}
${nombre_colaborador}
${correo_colaborador}
${area}
${puesto}
${lider}
${correo_lider}
${fecha_inicio}
${fecha_fin}
${dias_solicitados}
${motivo}
${fecha_solicitud}
${estatus}
${formato_recibido}
${observaciones_rh}
${firma_colaborador}
${firma_lider}
```

Para firmas, usa en Word exactamente:

```text
${firma_colaborador}
${firma_lider}
```

Cuando el documento se genere con firmas, esos placeholders se reemplazarán por la imagen PNG de la firma.

## 9. Rutas nuevas

```text
/admin/permisos/documentos/{solicitud}/inicial
/admin/permisos/documentos/{solicitud}/firmado
/admin/permisos/documentos/{solicitud}/reenviar-inicial
/admin/permisos/documentos/{solicitud}/reenviar-firmado-rh
```

## 10. Archivos generados

Los documentos se guardan en:

```text
storage/app/public/permisos/documentos/solicitud_ID/
```

Ejemplo:

```text
storage/app/public/permisos/documentos/solicitud_25/formato_permiso_inicial_20260604_120000.docx
storage/app/public/permisos/documentos/solicitud_25/formato_permiso_firmado_20260604_121500.docx
```

## 11. Flujo final esperado

```text
1. Colaborador llena solicitud.
2. Sistema genera DOCX inicial.
3. Sistema manda el DOCX a colaborador, líder y RH.
4. Colaborador firma con token.
5. Líder firma con token.
6. Sistema genera DOCX firmado con ambas firmas.
7. Sistema manda el DOCX firmado a RH.
8. RH marca formato recibido / pendiente / con observaciones.
```

