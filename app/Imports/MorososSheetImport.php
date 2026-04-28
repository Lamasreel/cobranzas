<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;

class MorososSheetImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows->skip(1) as $row) {

            $documento = $this->onlyNumbers($row[3] ?? null);

            if (!$documento) {
                continue;
            }

            $localidadId = $this->getLocalidadId($row[7] ?? null);

            DB::table('cliente')->updateOrInsert(
                [
                    'documento' => $documento,
                ],
                [
                    'titular_garantia'       => trim((string) ($row[1] ?? null)),
                    'documento_titular'      => $this->onlyNumbers($row[2] ?? null),
                    'nombre'                 => trim((string) ($row[4] ?? '')),
                    'calle'                  => trim((string) ($row[5] ?? '')),
                    'observaciones'          => trim((string) ($row[6] ?? '')),
                    'localidad'              => $localidadId,
                    'telefono'               => trim((string) ($row[8] ?? '')),
                    'telefono_1'             => trim((string) ($row[9] ?? '')),
                    'telefono_2'             => trim((string) ($row[10] ?? '')),
                    'empleador'              => trim((string) ($row[11] ?? '')),
                    'dias'                   => $this->toInt($row[12] ?? null),
                    'fecha_ultimo_pago'      => $this->toDate($row[13] ?? null),
                    'saldo_vencido'          => $this->toDecimal($row[14] ?? null),
                    'interes_punitorio'      => $this->toDecimal($row[15] ?? null),
                    'saldo_total'            => $this->toDecimal($row[16] ?? null),
                    'estado'                 => $this->estadoId($row[17] ?? null),

                    'feb'                    => $this->toDecimal($row[20] ?? null),
                    'feb_importe'            => $this->toDecimal($row[21] ?? null),
                    'mar'                    => $this->toDecimal($row[22] ?? null),
                    'mar_importe'            => $this->toDecimal($row[23] ?? null),

                    'datos_adicionales'      => trim((string) ($row[24] ?? '')),
                    'wsp'                    => $this->toDate($row[25] ?? null),
                    'tiene_wsp'              => $this->toSiNo($row[26] ?? null),
                    'sms'                    => $this->toDate($row[27] ?? null),
                    'llamada'                => $this->toDate($row[28] ?? null),
                    'tiene_tel'              => $this->toSiNo($row[29] ?? null),
                    'carta'                  => $this->toDate($row[30] ?? null),
                    'fecha_envio_carta'      => $this->toDate($row[31] ?? null),
                    'fecha_promesa_pago'     => $this->toDate($row[32] ?? null),
                    'observaciones_promesa'  => trim((string) ($row[33] ?? '')),
                ]
            );
        }
    }

    private function getLocalidadId($value): ?int
    {
        $nombre = trim((string) $value);

        if ($nombre === '') {
            return null;
        }

        return DB::table('localidad')
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombre)])
            ->orWhereRaw('LOWER(nombre_corto) = ?', [mb_strtolower($nombre)])
            ->value('id');
    }

    private function estadoId($value): int
    {
        $estado = mb_strtolower(trim((string) $value));

        if (str_contains($estado, 'pag')) {
            return 4;
        }

        if (str_contains($estado, 'promesa')) {
            return 3;
        }

        return 2;
    }

    private function toSiNo($value): ?int
    {
        $v = mb_strtolower(trim((string) $value));

        if ($v === '') return null;

        if (in_array($v, ['1', 'si', 'sí', 's', 'x', 'ok', 'true'], true)) {
            return 1;
        }

        return 2;
    }

    private function toDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function toDecimal($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $value = str_replace(['$', ' ', '.'], '', (string) $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }

    private function toInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) preg_replace('/\D/', '', (string) $value);
    }

    private function onlyNumbers($value): ?int
    {
        $num = preg_replace('/\D/', '', (string) $value);

        return $num !== '' ? (int) $num : null;
    }
}