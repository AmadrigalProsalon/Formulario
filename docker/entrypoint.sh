#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
  storage/logs \
  storage/app/public \
  storage/app/templates \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
  echo "ERROR: APP_KEY no está configurada." >&2
  exit 1
fi

if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
  echo "[1/6] Esperando a MySQL en ${DB_HOST:-db}:${DB_PORT:-3306}..."
  intento=0
  hasta=180

  until php -r '
    try {
        new PDO(
            "mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT") . ";dbname=" . getenv("DB_DATABASE") . ";charset=utf8mb4",
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD"),
            [PDO::ATTR_TIMEOUT => 3]
        );
        exit(0);
    } catch (Throwable $e) {
        exit(1);
    }
  ' >/dev/null 2>&1; do
    intento=$((intento + 1))
    if [ "$intento" -ge "$hasta" ]; then
      echo "ERROR: MySQL no estuvo disponible después de ${hasta} intentos." >&2
      exit 1
    fi
    sleep 2
  done
fi

echo "[2/6] Base de datos disponible."
php artisan optimize:clear || true

if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
  echo "[3/6] Ejecutando migraciones..."
  php artisan migrate --force --no-interaction
else
  echo "[3/6] Migraciones automáticas desactivadas."
fi

if [ "${AUTO_SEED:-true}" = "true" ]; then
  echo "[4/6] Revisando datos iniciales..."
  necesita_seed="$(php -r '
    try {
        $pdo = new PDO(
            "mysql:host=" . getenv("DB_HOST") . ";port=" . getenv("DB_PORT") . ";dbname=" . getenv("DB_DATABASE") . ";charset=utf8mb4",
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD")
        );
        $correo = getenv("RH_ADMIN_EMAIL") ?: "amadrigal@prosalon.mx";
        $admin = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE email = " . $pdo->quote($correo))->fetchColumn();
        $formularios = (int) $pdo->query("SELECT COUNT(*) FROM formularios")->fetchColumn();
        echo ($admin === 0 || $formularios === 0) ? "1" : "0";
    } catch (Throwable $e) {
        echo "1";
    }
  ')"

  if [ "$necesita_seed" = "1" ]; then
    echo "Ejecutando seeders..."
    php artisan db:seed --force --no-interaction
  else
    echo "Datos iniciales ya existentes; se omite el seeder."
  fi
else
  echo "[4/6] Seeders automáticos desactivados."
fi

echo "[5/6] Preparando almacenamiento y cachés..."
php artisan storage:link >/dev/null 2>&1 || true
php artisan config:cache || php artisan config:clear
php artisan view:cache || php artisan view:clear

echo "[6/6] Aplicación lista; iniciando PHP-FPM en el puerto 9000."
exec "$@"
