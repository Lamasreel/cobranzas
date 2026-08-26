<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$c = DB::connection('mysql_local');

echo 'morosos count: ' . $c->table('morosos')->count() . PHP_EOL;
echo 'hasTable morosos: ' . (Schema::connection('mysql_local')->hasTable('morosos') ? 'YES' : 'NO') . PHP_EOL;
echo 'hasTable jlv_parte_resumen: ' . (Schema::connection('mysql_local')->hasTable('jlv_parte_resumen') ? 'YES' : 'NO') . PHP_EOL;
echo 'hasTable promesas (local mysql): ' . (Schema::connection('mysql')->hasTable('promesas') ? 'YES' : 'NO') . PHP_EOL;
if (Schema::connection('mysql_local')->hasTable('jlv_parte_resumen')) {
    echo 'resumen count: ' . $c->table('jlv_parte_resumen')->count() . PHP_EOL;
    $r = $c->select('SHOW CREATE TABLE jlv_parte_resumen');
    echo print_r($r[0], true) . PHP_EOL;
}
echo 'cols morosos:' . PHP_EOL;
foreach ($c->getSchemaBuilder()->getColumnListing('morosos') as $col) { echo '  '.$col.PHP_EOL; }
echo 'triggers on jlv_parte:' . PHP_EOL;
print_r($c->select('SHOW TRIGGERS WHERE `Table` = \'jlv_parte\''));
echo 'join test count: ' . $c->table('morosos')
    ->leftJoin('jlv_parte_resumen as r','r.jlv_cod_cli','=',DB::raw('morosos.DNI'))
    ->select(DB::raw('count(*) total, SUM(COALESCE(r.pagado,0)) pagado'))->first()->total . PHP_EOL;

if (Schema::connection('mysql_local')->hasTable('jlv_parte_resumen')) {
    echo 'resumen count: ' . $c->table('jlv_parte_resumen')->count() . PHP_EOL;
    $r = $c->select('SHOW CREATE TABLE jlv_parte_resumen');
    echo (isset($r[0])) ? (is_object($r[0]) ? json_encode($r[0]) : print_r($r[0], true)) : '' . PHP_EOL;
}
echo 'triggers morosos: ' . PHP_EOL;
print_r($c->select("SHOW TRIGGERS WHERE `Table` = 'morosos'"));
echo 'cols morosos (DNI,NOMBRE,SAL_TOT,DEUDA,ESTADO,DIAS,INT_PUN,ORDEN,DNITIT): ' . PHP_EOL;
print_r($c->select("SHOW COLUMNS FROM morosos WHERE Field IN ('DNI','NOMBRE','SAL_TOT','DEUDA','ESTADO','DIAS','INT_PUN','ORDEN','DNITIT','TITGAR')"));
