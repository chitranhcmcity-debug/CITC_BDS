<?php

/**
 * Compatibility entry point.
 *
 * The complete, production-tested application is stored in ./legacy. Run it
 * behind the current /CITC_BDS URL so all original modules remain intact.
 */
$legacyPublic = __DIR__.'/legacy/public';
$legacyIndex = $legacyPublic.'/index.php';

if (! is_file($legacyIndex)) {
    http_response_code(503);
    exit('Không tìm thấy mã nguồn ứng dụng.');
}

chdir($legacyPublic);
require $legacyIndex;
