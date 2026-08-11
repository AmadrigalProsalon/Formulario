# Formularios RH — proporcionales automáticos

## Regla de vacaciones

1. La importación toma exclusivamente la columna **DIAS GANADOS AL DIA DE HOY MÁS LOS PROPORCIONALES DEL AÑO ACTUAL**.
2. RH indica la fecha de corte del archivo.
3. El sistema calcula diariamente el proporcional generado desde el día siguiente al corte hasta hoy.
4. El cálculo cambia automáticamente de tasa cuando el colaborador cumple aniversario.
5. Disponible hoy = ganado al corte + proporcional nuevo - días aprobados/usados - solicitudes en trámite.
6. Las vacaciones con fechas salteadas siguen contando una fecha seleccionada como un día.

## Instalación
```bash
cd ~/Formulario
docker compose down
unzip -o Formulario-RH-v2-proporcionales-automaticos.zip -d /tmp/formulario_nuevo
# Sustituye el contenido del proyecto conservando tu .env y respaldos.
docker compose up -d --build
docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan config:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
```

Después vuelve a importar el Excel indicando su fecha real de corte.
