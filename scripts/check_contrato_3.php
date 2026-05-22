<?php
$root = realpath(__DIR__ . '/..');
$paths = [
    $root . '/public/contratos/contrato_3.pdf',
    $root . '/public/storage/contratos/contrato_3.pdf',
    $root . '/storage/app/public/contratos/contrato_3.pdf',
];
$out = [];
foreach ($paths as $p) {
    $out[] = ['path' => $p, 'exists' => file_exists($p)];
}
echo json_encode($out, JSON_PRETTY_PRINT);
