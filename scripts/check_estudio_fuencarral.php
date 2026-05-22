<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
// Bootstrapping for DB facades
use Illuminate\Support\Facades\DB;

$p = DB::table('tbl_propiedad')->where('titulo_propiedad', 'Estudio Fuencarral')->first();
$alquileres = [];
if ($p) {
    $alquileres = DB::table('tbl_alquiler')->where('id_propiedad_fk', $p->id_propiedad)->get();
}
$out = ['propiedad' => $p, 'alquileres' => $alquileres];
echo json_encode($out, JSON_PRETTY_PRINT);
