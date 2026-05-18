<?php
// Actualiza url_pdf_contrato para id_contrato=4
require __DIR__ . '/../../vendor/autoload.php';
$host = '127.0.0.1';
$db   = 'spotstay';
$user = 'root';
$pass = '';
$charset = 'utf8';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare('SELECT url_pdf_contrato FROM tbl_contrato WHERE id_contrato = ?');
    $stmt->execute([4]);
    $old = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Old: " . ($old['url_pdf_contrato'] ?? 'NULL') . "\n";

    $new = 'http://localhost/SpotStay/public/storage/contratos/20260513_155239_contrato_4.pdf';
    $up = $pdo->prepare('UPDATE tbl_contrato SET url_pdf_contrato = ? WHERE id_contrato = ?');
    $up->execute([$new, 4]);
    echo "Updated to: $new\n";

    $stmt->execute([4]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Now: " . ($current['url_pdf_contrato'] ?? 'NULL') . "\n";
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
