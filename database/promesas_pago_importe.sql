-- ============================================================================
-- Agrega la columna importe_pagado a la tabla promesas_pago
-- (base sqlpremier, conexión mysql_local) si todavía no existe.
--
-- Requerido por: al marcar como pagado un moroso, se guarda el importe
-- efectivamente pagado junto con el resultado CUMPLIDO de la promesa.
--
-- Nota: si existen filas históricas con fechas '0000-00-00', MySQL en modo
-- estricto rechaza el ALTER; se desactiva el modo estricto de la sesión
-- (no modifica los datos).
--
-- Es idempotente: se puede correr manualmente.
-- ============================================================================

SET SESSION sql_mode = '';

SET @db_schema = DATABASE();

SET @sql = IF(
    (SELECT COUNT(*)
       FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = @db_schema
        AND TABLE_NAME = 'promesas_pago'
        AND COLUMN_NAME = 'importe_pagado') = 0,
    'ALTER TABLE promesas_pago ADD COLUMN importe_pagado DECIMAL(12,2) NULL DEFAULT NULL AFTER observaciones',
    'SELECT 1 AS ya_existe'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;