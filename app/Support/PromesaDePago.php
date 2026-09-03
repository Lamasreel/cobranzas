<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PromesaDePago
{
    /**
     * Conexión donde se persisten las promesas (base sqlpremier).
     */
    public static function connection(): string
    {
        return 'mysql_local';
    }

    /**
     * Normaliza una fecha de promesa a Y-m-d. Acepta d/m/Y, d-m-Y, d.m.Y y Y-m-d.
     */
    public static function normalizarFecha(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return null;
        }

        $formatos = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y'];

        foreach ($formatos as $formato) {
            try {
                $fecha = Carbon::createFromFormat($formato, $valor);
            } catch (\Throwable $e) {
                continue;
            }

            if ($fecha !== false && $fecha->format($formato) === $valor) {
                return $fecha->toDateString();
            }
        }

        return null;
    }

    /**
     * Devuelve la promesa pendiente (sin resultado CUMPLIDO/INCUMPLIDO) de un DNI, o null.
     */
    public static function promesaPendiente(string $dni): ?object
    {
        return DB::connection(self::connection())
            ->table('promesas_pago')
            ->where('dni', $dni)
            ->whereNull('resultado')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Indica si el DNI tiene una promesa pendiente (aún sin resultado).
     */
    public static function tienePromesaPendiente(string $dni): bool
    {
        return self::promesaPendiente($dni) !== null;
    }

    /**
     * Devuelve todo el historial de promesas del DNI (pendientes y resueltas),
     * ordenado de la más reciente a la más antigua.
     *
     * @return array<int, object>
     */
    public static function historial(string $dni): array
    {
        return DB::connection(self::connection())
            ->table('promesas_pago')
            ->where('dni', $dni)
            ->orderByDesc('id')
            ->get()
            ->all();
    }

    /**
     * Datos listos para mostrar en la UI: la promesa pendiente (si existe) y
     * el historial completo registrado en promesas_pago.
     *
     * @return array{pendiente: ?object, historial: array<int, object>}
     */
    public static function datosParaVista(string $dni): array
    {
        $historial = self::historial(trim($dni));
        $pendiente = null;

        foreach ($historial as $promesa) {
            $resultado = trim((string) ($promesa->resultado ?? ''));

            if ($resultado === '') {
                $pendiente = $promesa;
                break;
            }
        }

        return [
            'pendiente' => $pendiente,
            'historial' => $historial,
        ];
    }

    /**
     * Marca la promesa pendiente del DNI como CUMPLIDO registrando el importe
     * efectivamente pagado (columna importe_pagado de promesas_pago).
     *
     * Si el DNI no tiene una promesa pendiente, registra igualmente el pago en
     * promesas_pago como CUMPLIDO para que el importe quede guardado.
     */
    public static function marcarPagada(string $dni, float $importe): void
    {
        $dni = trim($dni);

        if ($dni === '') {
            throw new \InvalidArgumentException('El moroso no tiene DNI para registrar el pago.');
        }

        if ($importe < 0) {
            throw new \InvalidArgumentException('El importe pagado no puede ser negativo.');
        }

        $connection = DB::connection(self::connection());
        $pendiente = self::promesaPendiente($dni);

        if ($pendiente) {
            $connection->table('promesas_pago')
                ->where('id', $pendiente->id)
                ->update([
                    'resultado' => 'CUMPLIDO',
                    'importe_pagado' => $importe,
                ]);

            return;
        }

        $connection->table('promesas_pago')->insert([
            'dni' => $dni,
            'fecha_agendada' => now()->toDateString(),
            'fecha_prometida' => now()->toDateString(),
            'resultado' => 'CUMPLIDO',
            'importe_pagado' => $importe,
            'observaciones' => null,
        ]);
    }

    /**
     * Registra la promesa de pago de un moroso en la tabla promesas_pago de
     * sqlpremier (conexión mysql_local). Si el DNI ya tenía una promesa pendiente
     * (sin resultado), la actualiza en lugar de duplicarla.
     *
     * Estructura de la tabla: dni, fecha_agendada, fecha_prometida, resultado, observaciones.
     *
     * @param  object  $moroso  Fila de la tabla morosos (al menos DNI).
     */
    public static function upsertDesdeMoroso(object $moroso, string $fechaPromesa, ?string $observaciones = null): void
    {
        $fecha = self::normalizarFecha($fechaPromesa);

        if (!$fecha) {
            throw new \InvalidArgumentException("Fecha de promesa inválida: {$fechaPromesa}");
        }

        $connection = DB::connection(self::connection());
        $dni = trim((string) ($moroso->DNI ?? ''));

        if ($dni === '') {
            throw new \InvalidArgumentException('El moroso no tiene DNI para registrar la promesa.');
        }

        $valores = [
            'fecha_agendada' => now()->toDateString(),
            'fecha_prometida' => $fecha,
            'observaciones' => $observaciones !== '' ? $observaciones : null,
        ];

        $pendiente = self::promesaPendiente($dni);

        if ($pendiente) {
            $connection->table('promesas_pago')
                ->where('id', $pendiente->id)
                ->update($valores);

            return;
        }

        $connection->table('promesas_pago')->insert($valores + [
            'dni' => $dni,
            'resultado' => null,
        ]);
    }
}
