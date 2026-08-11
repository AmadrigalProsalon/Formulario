# Corrección V6 — CURP, RFC y correos

El importador reconoce directamente estas columnas del archivo de RH:

- `CURP`
- `RFC`
- `Direccion de Correo Colaborador`
- `JEFE DIRECTO`
- `Direccion de Correo Jefe`

Reglas:

1. El correo del colaborador se guarda en su propio registro.
2. El correo del jefe se guarda en el registro del líder asociado.
3. Los correos repetidos están permitidos y no crean empleados duplicados.
4. Un líder se identifica por su nombre normalizado; los espacios, acentos y mayúsculas no generan otro líder.
5. La importación busca empleados primero por número de empleado, después CURP, RFC y nombre.
6. Una celda vacía no borra un dato que ya estaba guardado.
7. CURP y RFC se normalizan en mayúsculas y sin espacios o guiones.

Después de actualizar el proyecto ejecute:

```powershell
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose restart app nginx
```

Después vuelva a importar el CSV.
