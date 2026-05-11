<?php
// Script para limpiar BD
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$db = $app->make('db');

// Eliminar las tablas problemáticas
$tablas = [
    'tbl_codigo_gestor',
    'tbl_asignacion_gestor', 
    'tbl_codigo_propiedad'
];

foreach ($tablas as $tabla) {
    try {
        $db->statement("DROP TABLE IF EXISTS $tabla");
        echo "✓ Eliminada tabla: $tabla\n";
    } catch (\Exception $e) {
        echo "✗ Error al eliminar $tabla: " . $e->getMessage() . "\n";
    }
}

echo "\nTablas eliminadas correctamente.\n";
