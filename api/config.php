<?php
// Caminhos dos mocks (ajuste se a sua estrutura mudar)
define('MOCK_FILE', __DIR__ . '/../data/oaes-sp-poc.json');
define('ALERTS_MOCK_FILE', __DIR__ . '/../data/obras-arte-alerts-jams.json');

// Quando for usar API real:
define('USE_MOCK_DEFAULT', true);     // true = usar mock por padrão
define('MOBFA_BASE_URL', '');         // ex.: 'https://api.mobfa...'
define('MOBFA_TOKEN', '');            // token real
define('OAES_ENDPOINT_PATH', '/');    // endpoint real de OAEs
define('ALERTS_ENDPOINT_PATH', '/');  // endpoint real de alerts
