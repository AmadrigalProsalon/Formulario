START TRANSACTION;

UPDATE permisos_solicitudes
SET estatus = 'formato_recibido',
    formato_recibido = 1,
    formato_recibido_at = COALESCE(formato_recibido_at, created_at, NOW()),
    observaciones_rh = NULL,
    updated_at = NOW();

COMMIT;

SELECT COUNT(*) AS solicitudes_aprobadas
FROM permisos_solicitudes
WHERE formato_recibido = 1
  AND estatus = 'formato_recibido';
