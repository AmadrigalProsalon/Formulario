#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")"
[ -f .env ] || cp .env.example .env
if ! grep -q '^APP_KEY=base64:' .env; then
  KEY="base64:$(openssl rand -base64 32)"
  sed -i "s|^APP_KEY=.*|APP_KEY=$KEY|" .env
fi
docker compose down
docker compose up -d --build
echo "Sistema listo en http://localhost:8092"
echo "Usuario: amadrigal@prosalon.mx"
echo "Contraseña: Admin123*"
