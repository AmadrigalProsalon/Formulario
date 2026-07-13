# Parche: el Excel manda en vacaciones

## Resultado
- `empleados.vacaciones_pendientes` es el saldo oficial importado desde Excel.
- El saldo ya no se recalcula con antigüedad + ajuste - usadas.
- Las solicitudes pendientes no descuentan saldo.
- RH descuenta el saldo únicamente al marcar el formato como recibido.
- Si RH revierte o cancela un formato recibido, el saldo se restaura.
- Los decimales se muestran con 2 posiciones.

## Instalación
```bash
cd ~/Formulario
cp -a app ../Formulario_app_backup_$(date +%Y%m%d_%H%M%S)
cp -a resources ../Formulario_resources_backup_$(date +%Y%m%d_%H%M%S)

unzip -o parche_excel_manda_vacaciones.zip -d .

docker compose up -d --build app nginx
docker exec -it formulario_rh_app php artisan config:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker compose restart app nginx
```

Después vuelve a importar el CSV para que cada saldo oficial quede sincronizado.
