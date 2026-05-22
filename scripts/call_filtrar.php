<?php
require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AlquilerController;

$app = require __DIR__ . '/../bootstrap/app.php';
// Necesario para inicializar facades y entorno
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Request::create('/admin/alquileres/filtrar', 'GET', ['page' => 1]);
$response = (new AlquilerController())->filtrar($request);
if ($response instanceof Illuminate\Http\JsonResponse) {
    echo json_encode($response->getData(), JSON_PRETTY_PRINT);
} else {
    echo (string) $response;
}
