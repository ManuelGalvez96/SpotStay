<?php
// Actualiza todas las URL que apunten a storage/contratos a la ruta pública con /SpotStay/public/storage/
require __DIR__ . '/../../vendor/autoload.php';
$host = '127.0.0.1';
$db   = 'spotstay';
$user = 'root';
$pass = '';
$charset = 'utf8';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->query("SELECT id_contrato, url_pdf_contrato FROM tbl_contrato WHERE url_pdf_contrato LIKE '%storage/contratos/%'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Encontrados: " . count($rows) . " registros potenciales.\n";

    $updates = [];
    foreach ($rows as $r) {
        $id = $r['id_contrato'];
        $url = trim($r['url_pdf_contrato']);
        if ($url === '') continue;

        // Si ya apunta a /SpotStay/public/storage, dejamos
        if (preg_match('#^https?://[^/]+/SpotStay/public/storage/contratos/(.+)$#i', $url)) {
            // correcto
            continue;
        }

        // Extraer el nombre de archivo
        if (preg_match('#^https?://[^/]+/storage/contratos/(.+)$#i', $url, $m)) {
            $file = $m[1];
        } elseif (preg_match('#^/storage/contratos/(.+)$#i', $url, $m)) {
            $file = $m[1];
        } elseif (preg_match('#^storage/contratos/(.+)$#i', $url, $m)) {
            $file = $m[1];
        } else {
            // No coincide
            continue;
        }

        $new = 'http://localhost/SpotStay/public/storage/contratos/' . $file;
        if ($new !== $url) {
            $updates[] = ['id' => $id, 'old' => $url, 'new' => $new];
        }
    }

    if (empty($updates)) {
        echo "No hay actualizaciones necesarias.\n";
        exit(0);
    }

    $pdo->beginTransaction();
    $upStmt = $pdo->prepare('UPDATE tbl_contrato SET url_pdf_contrato = ? WHERE id_contrato = ?');
    foreach ($updates as $u) {
        $upStmt->execute([$u['new'], $u['id']]);
        echo "Actualizado id={$u['id']}:\n  - De: {$u['old']}\n  - A:   {$u['new']}\n";
    }
    $pdo->commit();

    echo "Total actualizados: " . count($updates) . "\n";
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
