# Vacaciones y permisos — versión lista para despliegue

Esta versión parte del proyecto `Formulario-main (1).zip` entregado y mantiene su arquitectura actual.

## Cambios principales

### Solicitud de vacaciones
- Las vacaciones ya no dependen de un rango continuo.
- Se pueden seleccionar fechas consecutivas o salteadas.
- El número de días se calcula automáticamente según la cantidad de fechas seleccionadas.
- El backend recalcula los días y no confía en un valor escrito manualmente.
- Cada fecha se valida contra:
  - horario especial del empleado;
  - reglas especiales por empleado;
  - reglas de Punta Mita;
  - horario configurado del área;
  - reglas por área;
  - días festivos o inhábiles registrados.
- Se valida cruce contra solicitudes activas.
- La validación también corre nuevamente en servidor al enviar.

### Calendario de ausencias
- Las vacaciones con fechas salteadas aparecen solamente en los días realmente seleccionados.
- Se muestran máximo 3 personas por celda.
- Al hacer clic en un día se abre el detalle completo.
- El detalle muestra las fechas específicas de solicitudes con días salteados.
- Se eliminó la ruta pública de calendario de prueba.

### Horarios
Se incluyeron las reglas proporcionadas:
- Oficinas: L-V
- Acatlán: L-V
- Almacenistas Acatlán: L-V
- Almacén Vespertino: D-V
- Almacén Matutino: L-S
- Reglas especiales para Ricardo Baltazar, Miguel Corona, José Sanabria, Antonio Fernández, Juan José, Oscar Iván, Victor Manuel Santos, Jesús Cárdenas, Cesar Alejandro Rodriguez, Alejandro Pantoja, Isabel y Christoper Rosales.
- Reglas especiales de Punta Mita para Dulce, Delfina, Lizeth, Noemí, Cecilia, José, Valentín, Elsa, Ángel, César y Saúl.

La prioridad es:
1. horario especial guardado en el empleado;
2. regla especial por empleado;
3. regla especial de Punta Mita;
4. horario guardado en el área;
5. regla por área;
6. L-V por defecto.

### Importador de empleados/vacaciones
- Actualiza existentes por CLAVE, CURP, RFC o nombre.
- Crea nuevos empleados cuando no existen.
- Si un empleado no trae CLAVE, genera una estable.
- JEFE DIRECTO se marca como líder.
- Si el líder no existe, se crea con número automático.
- Lee correo de colaborador y correo de jefe.
- Preserva el estatus `es_lider` al actualizar.
- Las vacaciones históricas importadas siguen siendo reemplazables sin borrar solicitudes manuales.

## Instalación sobre una instalación existente

1. Respalda base de datos y proyecto.
2. Copia esta versión sobre el proyecto.
3. Reconstruye:

```bash
docker compose up -d --build app nginx
```

4. Ejecuta migraciones:

```bash
docker exec -it formulario_rh_app php artisan migrate --force
```

5. Limpia cachés:

```bash
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear
```

6. Reinicia:

```bash
docker compose restart app nginx
```

## Rutas importantes

- Formulario público: `/permisos/solicitud`
- Solicitudes RH: `/admin/permisos`
- Calendario: `/admin/ausencias/calendario`
- Empleados: `/admin/permisos-catalogos/empleados`
- Importador: `/admin/permisos-catalogos/empleados/importar`
- Horarios y festivos: `/admin/permisos-catalogos/calendario-laboral`

## Pruebas mínimas antes de publicar

### Caso 1 — días salteados
Selecciona vacaciones y agrega:
- 09/07/2026
- 13/07/2026
- 17/07/2026

Debe mostrar 3 días y en el calendario aparecer solo en esas tres fechas.

### Caso 2 — día fuera de horario
Con un empleado L-V intenta agregar sábado o domingo.
Debe rechazarse.

### Caso 3 — empleado que trabaja domingo
Con un empleado D-V intenta agregar domingo.
Debe aceptarse, salvo que esté registrado como inhábil/festivo.

### Caso 4 — festivo
Registra una fecha en `Horarios y días inhábiles`.
Intenta agregarla a vacaciones.
Debe rechazarse.

### Caso 5 — doble solicitud
Crea una solicitud activa para una fecha.
Intenta pedir la misma fecha nuevamente.
Debe rechazarse por cruce.

## Nota sobre saldos
Los días pendientes de formato no descuentan saldo. El descuento ocurre cuando RH marca `Formato recibido`, conservando la regla del sistema actual.
