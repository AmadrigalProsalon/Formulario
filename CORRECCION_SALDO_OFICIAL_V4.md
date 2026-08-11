# Corrección v4: saldo oficial de vacaciones

La importación usa exclusivamente la columna:

`DIAS GANADOS AL DIA DE HOY MÁS LOS PROPORCIONALES DEL AÑO ACTUAL`

Comportamiento:

- Normaliza acentos, espacios y el espacio final del encabezado del CSV.
- Conserva hasta 4 decimales durante la lectura.
- Guarda el valor en `vacaciones_ganadas_base` y `vacaciones_pendientes`.
- No resta otra vez las vacaciones históricas incluidas en el saldo del Excel.
- Suma automáticamente el proporcional generado después de la fecha de corte.
- Solo descuenta solicitudes aprobadas o pendientes cuya fecha sea posterior a la fecha de corte.
