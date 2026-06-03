# Parche módulo Vacaciones - Sistema Formularios RH

Este ZIP agrega un módulo especial de vacaciones al proyecto Laravel.

## Qué incluye

- Tabla `empleados`
- Tabla `vacaciones_solicitudes`
- Tabla `vacaciones_dias_inhabiles`
- Tabla `vacaciones_ajustes`
- Solicitud pública de vacaciones
- Consulta de días disponibles
- Validación para no solicitar más días de los disponibles
- Cálculo de días laborables descontando sábados, domingos y días inhábiles registrados
- Panel admin para aprobar/rechazar solicitudes
- Panel admin para empleados y saldos
- Panel admin para días inhábiles

## Cómo aplicar

1. Descomprime este ZIP.
2. Copia las carpetas sobre la raíz del proyecto Laravel.
3. En `routes/web.php`, agrega al final, antes o después de `require __DIR__ . '/auth.php';`:

```php
require __DIR__ . '/vacaciones.php';
```

4. En `resources/views/admin/layout.blade.php`, agrega estos enlaces al menú lateral:

```blade
<a href="{{ route('admin.vacaciones.index') }}"
   class="block px-4 py-3 rounded-xl transition {{ request()->routeIs('admin.vacaciones.*') ? 'bg-white text-slate-950' : 'text-slate-300 hover:bg-slate-800' }}">
    Vacaciones
</a>
```

Y en el menú móvil:

```blade
<a href="{{ route('admin.vacaciones.index') }}"
   class="px-3 py-2 rounded-lg whitespace-nowrap {{ request()->routeIs('admin.vacaciones.*') ? 'bg-white text-slate-950' : 'bg-slate-800' }}">
    Vacaciones
</a>
```

5. Ejecuta en el VPS:

```bash
cd ~/Formulario
docker compose up -d --build
docker exec -it formulario_rh_app php artisan migrate --force
docker exec -it formulario_rh_app php artisan optimize:clear
docker exec -it formulario_rh_app php artisan route:clear
docker exec -it formulario_rh_app php artisan view:clear
docker exec -it formulario_rh_app php artisan config:clear
```

## URLs nuevas

Formulario público:

```text
http://TU_IP:8092/vacaciones/solicitud
```

Admin solicitudes:

```text
http://TU_IP:8092/admin/vacaciones
```

Admin empleados:

```text
http://TU_IP:8092/admin/vacaciones/empleados
```

Admin días inhábiles:

```text
http://TU_IP:8092/admin/vacaciones/dias-inhabiles
```

## Cómo funciona el saldo

El sistema calcula:

```text
disponibles = días_totales - días_usados - días_pendientes
```

Donde:

- `días_totales` = días por antigüedad + ajustes manuales.
- `días_usados` = solicitudes aprobadas del año.
- `días_pendientes` = solicitudes pendientes del año.

Cuando un colaborador solicita vacaciones, la solicitud queda en `pendiente`, y esos días se bloquean para que no vuelva a solicitarlos.

## Regla de días por antigüedad

El cálculo inicial incluido es:

- 1 año: 12 días
- 2 años: 14 días
- 3 años: 16 días
- 4 años: 18 días
- 5 años: 20 días
- Desde el año 6: aumenta 2 días por cada 5 años adicionales

## Query rápido de empleado de prueba

```sql
INSERT INTO empleados (numero_empleado, nombre, correo, departamento, puesto, fecha_ingreso, activo, created_at, updated_at)
VALUES ('1001', 'Juan Pérez', 'juan.perez@prosalon.mx', 'Sistemas', 'Desarrollador', '2023-01-15', 1, NOW(), NOW());
```

## Nota

Este módulo no reemplaza el sistema multi-formularios. Lo complementa con lógica especial para vacaciones.
