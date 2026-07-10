-- Opcional: ejecutar solo si quieres revisar/ajustar líderes existentes.
-- El importador ya genera numero_empleado automático para líderes nuevos.

-- Asegurar columnas principales si el proyecto ya las usa.
-- En MySQL 8 funciona ADD COLUMN IF NOT EXISTS; si tu versión no lo soporta,
-- agrega estas columnas manualmente desde phpMyAdmin solo si faltan.
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS es_lider TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE empleados ADD COLUMN IF NOT EXISTS lider_id BIGINT UNSIGNED NULL;

-- Dar clave automática a líderes que ya existan sin número de empleado.
UPDATE empleados
SET numero_empleado = CONCAT('LIDER-', UPPER(SUBSTRING(MD5(TRIM(nombre)), 1, 10)))
WHERE es_lider = 1
  AND (numero_empleado IS NULL OR numero_empleado = '');
