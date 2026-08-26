<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ActualizarPagosResumen extends Command
{
    private string $connection = 'mysql_local';

    protected $signature = 'app:actualizar-pagos-resumen';

    protected $description = 'Reconstruye la tabla jlv_parte_resumen (total pagado por cliente según jlv_parte) en el servidor.';

    public function handle(): int
    {
        $db = DB::connection($this->connection);

        $this->info('Creando/verificando tabla jlv_parte_resumen...');

        $db->statement(
            'CREATE TABLE IF NOT EXISTS jlv_parte_resumen (
                jlv_cod_cli bigint(20) NOT NULL,
                pagado decimal(14,2) NOT NULL DEFAULT 0.00,
                cantidad_pagos int(11) NOT NULL DEFAULT 0,
                actualizado_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (jlv_cod_cli)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $this->info('Reconstruyendo datos (INSERT..SELECT server-side)...');

        $db->statement('TRUNCATE TABLE jlv_parte_resumen');

        $dnis = $db->table('morosos')
            ->whereNotNull('DNI')
            ->pluck('DNI')
            ->map(fn ($d) => (int) $d)
            ->unique()
            ->values();

        if ($dnis->isEmpty()) {
            $this->warn('No hay DNIs en morosos; resumen vacío.');

            return self::SUCCESS;
        }

        $ini = microtime(true);

        $db->statement(
            'INSERT INTO jlv_parte_resumen (jlv_cod_cli, pagado, cantidad_pagos)
             SELECT jlv_cod_cli,
                    SUM(COALESCE(jlv_importe, 0)) AS pagado,
                    COUNT(*) AS cantidad_pagos
             FROM jlv_parte
             WHERE jlv_cod_cli IN (' . $dnis->implode(',') . ')
             GROUP BY jlv_cod_cli'
        );

        $segundos = round(microtime(true) - $ini, 2);

        $totales = $db->selectOne('SELECT COUNT(*) total FROM jlv_parte_resumen');

        $this->info("Listo: {$totales->total} clientes en {$segundos} segundos.");

        return self::SUCCESS;
    }
}