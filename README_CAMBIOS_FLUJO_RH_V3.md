# Flujo de autorización RH — versión 3

Esta versión corrige el flujo de vacaciones y permisos sin borrar la base de datos existente.

## Nuevo comportamiento

1. Al registrar una solicitud queda **Pendiente de formato**.
2. RH puede abrir el detalle y:
   - subir el formato firmado; al hacerlo queda **Aprobada**;
   - rechazarla indicando el motivo;
   - eliminarla definitivamente.
3. Los movimientos importados desde Excel/CSV aparecen como **Registro histórico**, no como solicitudes aprobadas.
4. En **Empleados** existe la sección **Añadir empleado manualmente**, independiente del importador CSV.

## Actualización

Conservar los volúmenes existentes y ejecutar:

```bash
cd ~/Formulario
bash instalar.sh
```

El arranque ejecuta automáticamente la migración correctiva. No se debe usar `migrate:fresh` porque borraría la información existente.
