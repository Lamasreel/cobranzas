<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (['mysql_local' => 'morosos', 'mysql' => 'promesas_pago'] as $conn => $tbl) {
    try {
        $cols = DB::connection($conn)->getSchemaBuilder()->getColumnListing($tbl);
        echo $conn.'.'.$tbl.': '.implode(',', $cols).PHP_EOL;
        if ($conn === 'mysql_local') {
            foreach (preg_grep('/fecha|pago|cobr|estado/i', $cols) as $c) {
                try {
                    $t = DB::connection($conn)->getSchemaBuilder()->getColumnType($tbl, $c);
                } catch (Throwable $e) { $t = '?'; }
                echo '  - '.$c.' ('.$t.')'.PHP_EOL;
            }
        }
    } catch (Throwable $e) { echo $conn.'.'.$tbl.' ERR '.$e->getMessage().PHP_EOL; }
}
