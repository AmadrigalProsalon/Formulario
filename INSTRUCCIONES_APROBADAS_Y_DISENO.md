# Solicitudes aprobadas + formulario visual

## Incluye
- Todas las solicitudes existentes quedan como `Aprobada`.
- Se limpian `observaciones_rh` existentes.
- En una solicitud aprobada ya no aparecen botones, textareas ni notas en “Acciones RH”.
- Los registros históricos no muestran la leyenda técnica del importador como comentario.
- Listado y detalle rediseñados.
- Formulario de vacaciones inspirado en la referencia proporcionada.
- Se conservan:
  - días consecutivos o salteados;
  - validación de horario;
  - días festivos e inhábiles;
  - validación de duplicados;
  - saldo oficial desde Excel;
  - descuento exacto de las fechas seleccionadas.

## Instalación
```bash
cd ~/Formulario

cp -a app ../Formulario_app_backup_$(date +%Y%m%d_%H%M%S)
cp -a resources ../Formulario_resources_backup_$(date +%Y%m%d_%H%M%S)
cp -a database ../Formulario_database_backup_$(date +%Y%m%d_%H%M%S)

unzip -o parche_vacaciones_aprobadas_diseno_final.zip -d .

docker compose up -d --build app nginx
docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan config:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker compose restart app nginx
```

La migración actualiza automáticamente las solicitudes que ya existen.
