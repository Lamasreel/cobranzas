<?php

namespace App\Imports;

use App\Models\ClienteCarta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ClientesCartasImport implements ToCollection
{
    public int $insertados = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            $documentoTitular = trim((string) ($row[0] ?? ''));
            $documento = trim((string) ($row[1] ?? ''));
            $nombre = trim((string) ($row[2] ?? ''));
            $calle = trim((string) ($row[3] ?? ''));
            $observaciones = trim((string) ($row[4] ?? ''));
            $localidad = trim((string) ($row[5] ?? ''));

            if ($documento === '' && $nombre === '') {
                continue;
            }

            ClienteCarta::updateOrCreate(
                ['documento' => $documento],
                [
                    'documento_titular' => $documentoTitular,
                    'nombre' => $nombre,
                    'calle' => $calle,
                    'observaciones' => $observaciones,
                    'localidad' => $localidad,
                ]
            );

            $this->insertados++;
        }
    }
}