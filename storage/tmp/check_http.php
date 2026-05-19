<?php
$candidates = [
    'http://localhost/storage/contratos/20260513_155239_contrato_4.pdf',
    'http://localhost/SpotStay/storage/contratos/20260513_155239_contrato_4.pdf',
    'http://localhost/SpotStay/public/storage/contratos/20260513_155239_contrato_4.pdf',
    'http://localhost/public/storage/contratos/20260513_155239_contrato_4.pdf',
];

$opts = ['http' => ['method' => 'GET', 'timeout' => 5]];

foreach ($candidates as $url) {
    echo "=== Checking: $url ===\n";
    $headers = @get_headers($url, 1);
    if ($headers === false) {
        echo "NO_RESPONSE\n\n";
        continue;
    }

    if (is_array($headers)) {
        // First element is the status line
        $status = is_int(key($headers)) ? reset($headers) : ($headers[0] ?? '');
        echo (is_string($status) ? $status : print_r($status, true)) . "\n";
    }

    foreach ($headers as $k => $v) {
        if (is_int($k)) continue;
        echo "$k: " . (is_array($v) ? implode('; ', $v) : $v) . "\n";
    }

    echo "\n";
}
