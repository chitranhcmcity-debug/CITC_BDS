<?php

declare(strict_types=1);

$legacyEndpoint = dirname(__DIR__, 2).'/legacy/public/payment/payos_return.php';
if (! is_file($legacyEndpoint)) {
    http_response_code(503);
    exit('Payment endpoint is unavailable.');
}

chdir(dirname($legacyEndpoint));
require $legacyEndpoint;
