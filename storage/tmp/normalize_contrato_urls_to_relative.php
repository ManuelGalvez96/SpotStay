<?php
// Normaliza las URLs en tbl_contrato a rutas relativas: storage/contratos/<file>
require __DIR__ . '/../../vendor/autoload.php';
$host = '127.0.0.1';
$db   = 'spotstay';
$user = 'root';
$pass = '';
$charset = 'utf8';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->query("SELECT id_contrato, url_pdf_contrato FROM tbl_contrato WHERE url_pdf_contrato IS NOT NULL AND url_pdf_contrato != ''");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Revisando: " . count($rows) . " registros.\n";

    $updates = [];
    foreach ($rows as $r) {
        $id = $r['id_contrato'];
        $url = trim($r['url_pdf_contrato']);
        $normalized = null;

        // Casos comunes:
        // 1) http://localhost/SpotStay/public/storage/contratos/file.pdf
        // 2) http://localhost/storage/contratos/file.pdf
        // 3) /storage/contratos/file.pdf
        // 4) storage/contratos/file.pdf
        if (preg_match('#/storage/contratos/(.+)$#i', $url, $m)) {
            $file = $m[1];
            $normalized = 'storage/contratos/' . $file;
        } elseif (preg_match('#^https?://[^/]+/.+/storage/contratos/(.+)$#i', $url, $m)) {
            $file = $m[1];
            $normalized = 'storage/contratos/' . $file;
        } elseif (preg_match('#^https?://[^/]+/storage/contratos/(.+)$#i', $url, $m)) {
            $file = $m[1];
            $normalized = 'storage/contratos/' . $file;
        }

        if ($normalized && $normalized !== $url) {
            $updates[] = ['id' => $id, 'old' => $url, 'new' => $normalized];
        }
    }

    if (empty($updates)) {
        echo "No hay registros que normalizar.\n";
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
