<?php
$root = realpath(__DIR__ . '/..');
if ($argc < 2) { echo "usage: php check_file.php filename\n"; exit(1); }
$name = $argv[1];
$path = $root . '/storage/app/public/contratos/' . $name;
echo json_encode(['path' => $path, 'exists' => file_exists($path)], JSON_PRETTY_PRINT) . PHP_EOL;
