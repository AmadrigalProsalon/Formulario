# Arranque automático

Desde la carpeta del proyecto ejecute únicamente:

```bash
bash instalar.sh
```

El instalador:

1. valida Docker y la red `root_default` de Traefik;
2. construye la imagen;
3. espera a MySQL;
4. ejecuta migraciones y seeders;
5. espera a que PHP-FPM esté saludable;
6. inicia Nginx;
7. publica `https://rh.prosalongroup.com`.

El primer arranque puede tardar varios minutos mientras se crea la base de datos y se cargan los datos iniciales. Si algo falla, el instalador imprime automáticamente los últimos logs del contenedor de la aplicación.
