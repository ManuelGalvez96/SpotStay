<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$usuario = DB::table('tbl_usuario')->where('nombre_usuario', 'Carlos Garcia')->first();
if (!$usuario) {
    echo "NO_USER\n";
    exit(0);
}

$esGestor = DB::table('tbl_propiedad')
    ->where('id_gestor_fk', $usuario->id_usuario)
    ->exists();

$arrendadorId = DB::table('tbl_usuario')
    ->where('id_usuario', $usuario->id_usuario)
    ->value('id_usuario');

echo 'ID=' . $arrendadorId . PHP_EOL;
echo 'ES_GESTOR=' . ($esGestor ? 'SI' : 'NO') . PHP_EOL;
