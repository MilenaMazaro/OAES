<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/config.php';

$useMock = isset($_GET['mock']) ? (($_GET['mock'] === '1') || (strtolower($_GET['mock']) === 'true')) : USE_MOCK_DEFAULT;

try {
    if ($useMock) {
        if (!file_exists(MOCK_FILE)) { http_response_code(500); echo json_encode(array('error'=>'Mock não encontrado')); exit; }
        $raw = file_get_contents(MOCK_FILE);
        if ($raw === false) { http_response_code(500); echo json_encode(array('error'=>'Falha ao ler mock')); exit; }
        $data = json_decode($raw, true);
    } else {
        if (!MOBFA_BASE_URL || !MOBFA_TOKEN) { http_response_code(500); echo json_encode(array('error'=>'MOBFA_BASE_URL/MOBFA_TOKEN não configurados')); exit; }
        $endpoint = rtrim(MOBFA_BASE_URL,'/') . OAES_ENDPOINT_PATH;

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token ' . MOBFA_TOKEN, 'Accept: application/json'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $resp = curl_exec($ch);
        if ($resp === false) { http_response_code(502); echo json_encode(array('error'=>'Erro cURL: '.curl_error($ch))); curl_close($ch); exit; }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code < 200 || $code >= 300) { http_response_code($code); echo $resp; exit; }

        $data = json_decode($resp, true);
    }

    // Normaliza para FeatureCollection
    $out = array('type' => 'FeatureCollection', 'features' => array());

    if (isset($data['type']) && strtolower($data['type']) === 'featurecollection' && isset($data['features'])) {
        $out = $data;
    } else if (isset($data['jams']) && is_array($data['jams'])) {
        foreach ($data['jams'] as $jam) {
            if (!isset($jam['feature'])) continue;
            $feat = $jam['feature'];
            if (!isset($feat['type']) || strtolower($feat['type']) !== 'feature') continue;

            if (!isset($feat['properties']) || !is_array($feat['properties'])) $feat['properties'] = array();
            $feat['properties']['oae_name'] = isset($jam['name'])   ? $jam['name']   : null;
            $feat['properties']['oae_type'] = isset($jam['type'])   ? $jam['type']   : null;
            $feat['properties']['street']   = isset($jam['street']) ? $jam['street'] : null;
            $feat['properties']['end']      = isset($jam['end'])    ? $jam['end']    : null;
            $feat['properties']['length']   = isset($jam['length']) ? $jam['length'] : null;
            $feat['properties']['speed']    = isset($jam['speed'])  ? $jam['speed']  : null;
            $feat['properties']['level']    = isset($jam['level'])  ? $jam['level']  : null;

            $out['features'][] = $feat;
        }
    } else if (is_array($data) && isset($data[0]['type']) && $data[0]['type']==='Feature') {
        $out['features'] = $data;
    } else {
        $out = $data; // formato diferente → retorna bruto
    }

    echo json_encode($out);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error'=>$e->getMessage()));
}
