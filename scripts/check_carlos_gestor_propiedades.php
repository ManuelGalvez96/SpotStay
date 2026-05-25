<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$usuario = DB::table('tbl_usuario')
    ->where('nombre_usuario', 'Carlos Garcia')
    ->first();

if (!$usuario) {
    echo "NO_USER\n";
    exit(0);
}

$propiedades = DB::table('tbl_propiedad')
    ->select('id_propiedad', 'titulo_propiedad', 'id_gestor_fk', 'id_arrendador_fk')
    ->where('id_gestor_fk', $usuario->id_usuario)
    ->get();

echo 'ID=' . $usuario->id_usuario . PHP_EOL;
echo 'GESTOR_COUNT=' . $propiedades->count() . PHP_EOL;

foreach ($propiedades as $propiedad) {
    echo $propiedad->id_propiedad . '|' . $propiedad->titulo_propiedad . '|gestor=' . $propiedad->id_gestor_fk . '|arrendador=' . $propiedad->id_arrendador_fk . PHP_EOL;
}
