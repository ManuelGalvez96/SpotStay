<?php

// Script temporal para obtener url_pdf_contrato del contrato 4
require __DIR__ . '/../../vendor/autoload.php';

$host = '127.0.0.1';
$db   = 'spotstay';
$user = 'root';
$pass = '';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $stmt = $pdo->prepare('SELECT url_pdf_contrato FROM tbl_contrato WHERE id_contrato = ?');
    $stmt->execute([4]);
    $row = $stmt->fetch();
    if ($row && isset($row['url_pdf_contrato'])) {
        echo $row['url_pdf_contrato'];
    } else {
        echo "NULL\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
