<?php
/* =========================
   CONFIG • compatível PHP 5
   ========================= */

// ---- Caminhos dos mocks que você já usa
if (!defined('MOCK_FILE'))          define('MOCK_FILE', __DIR__ . '/../data/oaes-sp-poc.json');
if (!defined('ALERTS_MOCK_FILE'))   define('ALERTS_MOCK_FILE', __DIR__ . '/../data/obras-arte-alerts-jams.json');

// ---- Helper env() (mantém o seu)
if (!function_exists('env')) {
    function env($key, $default = '') {
        $val = getenv($key);
        return $val !== false ? $val : $default;
    }
}

// ---- Converte env para booleano de forma segura (PHP 5)
if (!function_exists('env_bool')) {
    function env_bool($key, $default_true) {
        $raw = env($key, $default_true ? '1' : '0'); // string
        $raw_l = strtolower(trim((string)$raw));
        // aceita: "1", "true", "on", "yes"
        return ($raw_l === '1' || $raw_l === 'true' || $raw_l === 'on' || $raw_l === 'yes');
    }
}

// ---- Configs de API real (mantém o seu)
if (!defined('USE_MOCK_DEFAULT'))     define('USE_MOCK_DEFAULT', env_bool('USE_MOCK_DEFAULT', true));
if (!defined('MOBFA_BASE_URL'))       define('MOBFA_BASE_URL',   env('MOBFA_BASE_URL', '')); // ex: https://api.mobfa...
if (!defined('MOBFA_TOKEN'))          define('MOBFA_TOKEN',      env('MOBFA_TOKEN', ''));    // token real
if (!defined('OAES_ENDPOINT_PATH'))   define('OAES_ENDPOINT_PATH',   env('OAES_ENDPOINT_PATH', '/'));
if (!defined('ALERTS_ENDPOINT_PATH')) define('ALERTS_ENDPOINT_PATH', env('ALERTS_ENDPOINT_PATH', '/'));

// ---- Diretório de dados JSON (CRUD simples)
if (!defined('DATA_DIR')) define('DATA_DIR', __DIR__ . '/../data');

// Arquivos usados pelos endpoints de tipos/indicadores
if (!defined('INDICATORS_FILE'))            define('INDICATORS_FILE', DATA_DIR . '/indicators.json');
if (!defined('OAE_TYPES_FILE'))             define('OAE_TYPES_FILE', DATA_DIR . '/oae_types.json');
if (!defined('OAE_TYPE_INDICATORS_FILE'))   define('OAE_TYPE_INDICATORS_FILE', DATA_DIR . '/oae_type_indicadores.json'); // <- usa seu nome de arquivo

// ---- Helpers de JSON (compatíveis PHP 5)
if (!function_exists('json_read')) {
    function json_read($path) {
        if (!file_exists($path)) return array();
        $txt = @file_get_contents($path);
        if ($txt === false || $txt === '') return array();
        $data = json_decode($txt, true);
        return is_array($data) ? $data : array();
    }
}

if (!function_exists('json_write')) {
    function json_write($path, $data) {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        // No PHP 5, sem flags JSON; simples já resolve
        @file_put_contents($path, json_encode($data));
    }
}

// ---- Lê corpo da requisição (JSON ou x-www-form-urlencoded) — útil pra PUT/DELETE
if (!function_exists('body_json')) {
    function body_json() {
        $raw = @file_get_contents('php://input');
        if ($raw === false || $raw === '') return array();

        // Tenta decodificar JSON
        $data = json_decode($raw, true);
        if (is_array($data)) return $data;

        // Fallback: tentar como query string (x-www-form-urlencoded)
        $arr = array();
        parse_str($raw, $arr);
        if (is_array($arr)) return $arr;

        return array();
    }
}


if (!function_exists('effective_method')) {
    function effective_method() {
        $m = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
        if ($m === 'POST') {
            // verifica query string
            if (isset($_GET['_method'])) {
                $ov = strtoupper($_GET['_method']);
                if ($ov === 'PUT' || $ov === 'DELETE') return $ov;
            }
            // verifica corpo (application/x-www-form-urlencoded)
            $b = body_json(); // também lê form-urlencoded no fallback
            if (isset($b['_method'])) {
                $ov = strtoupper($b['_method']);
                if ($ov === 'PUT' || $ov === 'DELETE') return $ov;
            }
        }
        return $m;
    }
}
