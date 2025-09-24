<?php
// Caminhos dos mocks
define('MOCK_FILE', __DIR__ . '/../data/oaes-sp-poc.json');
define('ALERTS_MOCK_FILE', __DIR__ . '/../data/obras-arte-alerts-jams.json');

// Função helper para pegar env ou fallback
function env($key, $default = '') {
    $val = getenv($key);
    return $val !== false ? $val : $default;
}

// Quando for usar API real:
define('USE_MOCK_DEFAULT', env('USE_MOCK_DEFAULT', true));
define('MOBFA_BASE_URL',   env('MOBFA_BASE_URL', '')); // ex: https://api.mobfa...
define('MOBFA_TOKEN',      env('MOBFA_TOKEN', ''));    // token real
define('OAES_ENDPOINT_PATH', env('OAES_ENDPOINT_PATH', '/'));
define('ALERTS_ENDPOINT_PATH', env('ALERTS_ENDPOINT_PATH', '/'));
