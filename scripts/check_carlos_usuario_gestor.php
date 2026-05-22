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

$gestor = DB::table('tbl_usuario_gestor')
    ->where('id_usuario_gestor', $usuario->id_usuario)
    ->first();

echo 'ID=' . $usuario->id_usuario . PHP_EOL;
echo 'USUARIO_GESTOR=' . ($gestor ? 'SI' : 'NO') . PHP_EOL;
