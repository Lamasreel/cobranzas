<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class MorososExcelImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        (new MorososSheetImport())->collection($rows);
    }
}