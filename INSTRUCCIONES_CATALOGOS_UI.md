# Mejora visual de catálogos RH

Incluye rediseño de empleados, horarios/días inhábiles, áreas, tipos de permiso y paginación personalizada.

## Instalar
```bash
cd ~/Formulario
cp -a resources ../Formulario_resources_backup_$(date +%Y%m%d_%H%M%S)
unzip -o parche_catalogos_ui_final.zip -d .
docker compose up -d --build app nginx
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan route:clear
docker compose restart app nginx
```

No requiere migraciones ni modifica datos.
