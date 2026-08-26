-- ============================================================================
-- Provisión de la tabla resumen de pagos: jlv_parte_resumen
--
-- La tabla de movimientos jlv_parte tiene ~1.3M de filas y agregarla en vivo
-- (SUM(jlv_importe) GROUP BY jlv_cod_cli) demora varios minutos en el servidor
-- MySQL 5.5, por eso se mantiene un resumen por cliente (jlv_cod_cli == DNI de
-- morosos). La página de morosos hace LEFT JOIN de morosos con esta tabla.
--
-- Este script es idempotente: se puede correr manualmente o mediante
--   php artisan app:actualizar-pagos-resumen
--
-- Conexión: mysql_local (base sqlpremier)
-- ============================================================================

CREATE TABLE IF NOT EXISTS jlv_parte_resumen (
  jlv_cod_cli bigint(20) NOT NULL,
  pagado decimal(14,2) NOT NULL DEFAULT 0.00,
  cantidad_pagos int(11) NOT NULL DEFAULT 0,
  actualizado_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (jlv_cod_cli)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Carga inicial / reconstrucción (server-side, sin transferir datos por red).
-- IMPORTANTE: se restringe a los DNIs de la tabla morosos (el cuerpo de la
-- consulta se ejecuta AGREGADO EN EL SERVIDOR en ~5-10 seg). Una agregación
-- sobre las 1.3M de filas completas de jlv_parte tardaría 20+ minutos en este
-- servidor y provoca cortes de conexión.
TRUNCATE TABLE jlv_parte_resumen;

INSERT INTO jlv_parte_resumen (jlv_cod_cli, pagado, cantidad_pagos)
SELECT jlv_cod_cli,
       SUM(COALESCE(jlv_importe, 0)) AS pagado,
       COUNT(*) AS cantidad_pagos
FROM jlv_parte
WHERE jlv_cod_cli IN (SELECT DNI FROM morosos WHERE DNI IS NOT NULL)
GROUP BY jlv_cod_cli;

-- ============================================================================
--  Triggers para mantener el resumen ACTUALIZADO EN TIEMPO REAL
--  (cada alta/baja/cambio en jlv_parte refleja al instante en el resumen)
-- ============================================================================

DROP TRIGGER IF EXISTS trg_jlv_parte_ai;
CREATE TRIGGER trg_jlv_parte_ai AFTER INSERT ON jlv_parte FOR EACH ROW
  INSERT INTO jlv_parte_resumen (jlv_cod_cli, pagado, cantidad_pagos)
  VALUES (NEW.jlv_cod_cli, COALESCE(NEW.jlv_importe, 0), 1)
  ON DUPLICATE KEY UPDATE
    pagado = pagado + VALUES(pagado),
    cantidad_pagos = cantidad_pagos + 1;

DROP TRIGGER IF EXISTS trg_jlv_parte_ad;
CREATE TRIGGER trg_jlv_parte_ad AFTER DELETE ON jlv_parte FOR EACH ROW
  UPDATE jlv_parte_resumen
  SET pagado = pagado - COALESCE(OLD.jlv_importe, 0),
      cantidad_pagos = cantidad_pagos - 1
  WHERE jlv_cod_cli = OLD.jlv_cod_cli;