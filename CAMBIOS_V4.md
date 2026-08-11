# Cambios v4

- RH puede aprobar solicitudes sin adjuntar formato firmado.
- El formato firmado queda como documento opcional y puede cargarse antes o después de aprobar.
- Se conserva la opción de rechazar solicitudes.
- Se ocultó `Permiso médico` del formulario público y se desactiva automáticamente en instalaciones existentes.
- Se añadió exportación Excel desde **Empleados y saldos**.
- El Excel incluye dos hojas: **Empleados y saldos** e **Historial vacaciones**.
- La exportación respeta los filtros actuales de búsqueda, departamento y estado.

## Actualización

Ejecuta únicamente:

```bash
cd ~/Formulario
bash instalar.sh
```

El instalador conserva los volúmenes existentes y ejecuta las migraciones pendientes.
