# Parche final: vacaciones + días salteados + UI + menú

## Incluye
- El formulario público `/permisos/solicitud` vuelve a mostrar el selector de fechas individuales.
- Permite fechas consecutivas o salteadas.
- Cada fecha elegida equivale exactamente a 1 día a descontar.
- El backend valida que `dias_solicitados` coincida con las fechas elegidas.
- Valida horario laboral, festivos, inhábiles, duplicados y cruces.
- El saldo oficial viene de `empleados.vacaciones_pendientes`, cargado desde Excel.
- El panel RH tiene un menú reorganizado, con mejor jerarquía, iconos y menú móvil.
- Los saldos se muestran con dos decimales.

## Instalar
```bash
cd ~/Formulario

cp -a app ../Formulario_app_backup_$(date +%Y%m%d_%H%M%S)
cp -a resources ../Formulario_resources_backup_$(date +%Y%m%d_%H%M%S)

unzip -o parche_vacaciones_ui_final.zip -d .

docker compose up -d --build app nginx
docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan config:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker compose restart app nginx
```

## Prueba mínima
1. Abre `/permisos/solicitud`.
2. Selecciona un colaborador.
3. Elige `Vacaciones`.
4. Agrega, por ejemplo, 19/07/2026, 22/07/2026 y 25/07/2026.
5. Debe mostrar `3 día(s)` y `Días solicitados = 3`.
6. Al guardar, solo esas tres fechas se almacenan en `permiso_solicitud_dias`.
