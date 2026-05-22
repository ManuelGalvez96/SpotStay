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
    $sql = "SELECT tbl_alquiler.id_alquiler, COALESCE(c.url_pdf_contrato,'') as url_pdf_contrato FROM tbl_alquiler LEFT JOIN tbl_contrato c ON tbl_alquiler.id_alquiler = c.id_alquiler_fk WHERE tbl_alquiler.id_alquiler = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([2]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($row, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
