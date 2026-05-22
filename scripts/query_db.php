<?php
$env = parse_ini_file(__DIR__ . '/../.env');
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$db = $env['DB_DATABASE'] ?? 'spotstay';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare("SELECT * FROM tbl_propiedad WHERE titulo_propiedad = ?");
    $stmt->execute(['Estudio Fuencarral']);
    $prop = $stmt->fetch(PDO::FETCH_ASSOC);

    $alquileres = [];
    if ($prop) {
        $stmt = $pdo->prepare("SELECT * FROM tbl_alquiler WHERE id_propiedad_fk = ?");
        $stmt->execute([$prop['id_propiedad']]);
        $alquileres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($alquileres as &$a) {
            $stmt = $pdo->prepare("SELECT * FROM tbl_contrato WHERE id_alquiler_fk = ?");
            $stmt->execute([$a['id_alquiler']]);
            $a['contrato'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    echo json_encode(['propiedad' => $prop, 'alquileres' => $alquileres], JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
