<?php
header('Content-Type: text/plain; charset=utf-8');

$keys = array('MOBFA_BASE_URL','MOBFA_TOKEN','OAES_ENDPOINT_PATH','USE_MOCK_DEFAULT','MOCK_FILE');
foreach ($keys as $k) {
    $v = getenv($k);
    echo $k.': '.($v !== false ? $v : '[VAZIO]')."\n";
}

