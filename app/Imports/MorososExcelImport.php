<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MorososExcelImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Aguilares'  => new MorososSheetImport(),
            'Concepcion' => new MorososSheetImport(),
            'Alberdi'    => new MorososSheetImport(),
            'LaCocha'   => new MorososSheetImport(),
            'SantaAna'  => new MorososSheetImport(),
            'Sarmientos'  => new MorososSheetImport(),
            'Otros'  => new MorososSheetImport(),
        ];
    }
}