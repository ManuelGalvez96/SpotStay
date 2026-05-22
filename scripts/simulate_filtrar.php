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
    $sql = "SELECT tbl_alquiler.id_alquiler, tbl_alquiler.id_propiedad_fk, tbl_alquiler.id_inquilino_fk, tbl_alquiler.estado_alquiler, tbl_alquiler.fecha_inicio_alquiler, tbl_alquiler.fecha_fin_alquiler, tbl_propiedad.titulo_propiedad, tbl_propiedad.ciudad_propiedad, tbl_propiedad.precio_propiedad, inquilino.nombre_usuario as nombre_inquilino, arrendador.id_usuario as id_arrendador, arrendador.nombre_usuario as nombre_arrendador, COALESCE(c.url_pdf_contrato,'') as url_pdf_contrato FROM tbl_alquiler JOIN tbl_propiedad ON tbl_alquiler.id_propiedad_fk = tbl_propiedad.id_propiedad JOIN tbl_usuario inquilino ON tbl_alquiler.id_inquilino_fk = inquilino.id_usuario JOIN tbl_usuario arrendador ON tbl_propiedad.id_arrendador_fk = arrendador.id_usuario LEFT JOIN tbl_contrato c ON tbl_alquiler.id_alquiler = c.id_alquiler_fk WHERE tbl_alquiler.id_alquiler = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([2]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo json_encode(['error'=>'not found']); exit; }
    $rutaRel = ltrim($row['url_pdf_contrato'],'/\\');
    $candidates = [__DIR__ . '/../public/' . $rutaRel, __DIR__ . '/../public/storage/' . $rutaRel, __DIR__ . '/../storage/app/public/' . $rutaRel];
    $exists = false;
    foreach ($candidates as $p) { if (file_exists($p)) { $exists = true; break; } }
    $row['file_exists'] = $exists;
    $row['candidates'] = $candidates;
    $row['download_route'] = $exists ? '/admin/alquileres/'.$row['id_alquiler'].'/descargar-contrato' : '';
    echo json_encode($row, JSON_PRETTY_PRINT);
} catch (Exception $e) { echo json_encode(['error'=>$e->getMessage()]); }
