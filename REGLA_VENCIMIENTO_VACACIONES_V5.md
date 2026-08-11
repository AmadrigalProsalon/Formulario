# Bolsas anuales y vencimiento de vacaciones

Esta versión divide el saldo oficial importado en dos bolsas:

- **Saldo del año anterior:** disponible del 1 de enero al 30 de abril.
- **Saldo del año actual:** proporcional que continúa creciendo diariamente.

## Reglas

1. De enero a abril se pueden combinar ambas bolsas.
2. Cada día solicitado consume primero el saldo del año anterior.
3. El 1 de mayo el remanente del año anterior vence automáticamente en el cálculo.
4. La validación usa la fecha real de cada día solicitado, incluso para días salteados.
5. Una solicitud para mayo no puede usar saldo vencido aunque se capture en abril.
6. El Excel sigue siendo la fuente oficial mediante la columna:
   `DIAS GANADOS AL DIA DE HOY MÁS LOS PROPORCIONALES DEL AÑO ACTUAL`.

## Separación automática al importar

- Si la fecha de corte está entre enero y abril, el sistema calcula el proporcional del año actual y considera el excedente como saldo del año anterior.
- Si la fecha de corte es mayo o posterior, todo el saldo oficial se considera vigente del año actual porque el saldo anterior ya debió vencer.

## Actualización

```powershell
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose restart app nginx
```

No use `docker compose down -v` para actualizar una instalación con datos.
