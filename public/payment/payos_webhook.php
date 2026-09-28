<?php

declare(strict_types=1);

$legacyEndpoint = dirname(__DIR__, 2).'/legacy/public/payment/payos_webhook.php';
if (! is_file($legacyEndpoint)) {
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 1, 'message' => 'Payment endpoint is unavailable.']);
    exit;
}

chdir(dirname($legacyEndpoint));
require $legacyEndpoint;
