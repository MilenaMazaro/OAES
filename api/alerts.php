<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/config.php';

$useMock = isset($_GET['mock']) ? (($_GET['mock'] === '1') || (strtolower($_GET['mock']) === 'true')) : USE_MOCK_DEFAULT;
$oaeName = isset($_GET['oae_name']) ? trim($_GET['oae_name']) : '';

function append_point(&$out, $ptProps, $lng, $lat) {
    $out['features'][] = array(
        'type' => 'Feature',
        'properties' => $ptProps,
        'geometry' => array('type' => 'Point', 'coordinates' => array($lng, $lat))
    );
}

try {
    if ($useMock) {
        if (!file_exists(ALERTS_MOCK_FILE)) { http_response_code(500); echo json_encode(array('error'=>'Mock de alerts não encontrado')); exit; }
        $raw = file_get_contents(ALERTS_MOCK_FILE);
        if ($raw === false) { http_response_code(500); echo json_encode(array('error'=>'Falha ao ler mock de alerts')); exit; }
        $data = json_decode($raw, true);
    } else {
        if (!MOBFA_BASE_URL || !MOBFA_TOKEN) { http_response_code(500); echo json_encode(array('error'=>'MOBFA_BASE_URL/MOBFA_TOKEN não configurados')); exit; }
        $endpoint = rtrim(MOBFA_BASE_URL,'/') . ALERTS_ENDPOINT_PATH;

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

    $out = array('type' => 'FeatureCollection', 'features' => array());

    // Se já vier em FeatureCollection
    if (isset($data['type']) && strtolower($data['type']) === 'featurecollection' && isset($data['features'])) {
        foreach ($data['features'] as $f) {
            // filtro por nome da OAE (se houver)
            if ($oaeName) {
                $n = strtolower(trim(isset($f['properties']['oae_name']) ? $f['properties']['oae_name'] : (isset($f['properties']['name']) ? $f['properties']['name'] : '')));
                if ($n !== strtolower($oaeName)) continue;
            }
            if (isset($f['geometry']['type']) && $f['geometry']['type']==='Point') {
                $out['features'][] = $f;
            }
        }
        echo json_encode($out); exit;
    }

    // Formato {alerts:[...]}
    if (isset($data['alerts']) && is_array($data['alerts'])) {
        foreach ($data['alerts'] as $a) {
            // point como Feature
            if (isset($a['point']) && isset($a['point']['type']) && $a['point']['type']==='Feature'
                && isset($a['point']['geometry']['type']) && $a['point']['geometry']['type']==='Point') {

                $n = isset($a['name']) ? $a['name'] : (isset($a['point']['properties']['name']) ? $a['point']['properties']['name'] : '');
                if ($oaeName && strtolower(trim($n)) !== strtolower($oaeName)) continue;

                $feat = $a['point'];
                if (!isset($feat['properties']) || !is_array($feat['properties'])) $feat['properties'] = array();

                $keys = array('type','alert_type','subtype','roadType','reportDescription','created','street','city','name');
                foreach ($keys as $k) { if (isset($a[$k])) $feat['properties'][$k] = $a[$k]; }

                $out['features'][] = $feat;
                continue;
            }

            // fallback lat/lng soltos
            if (isset($a['location']['x']) && isset($a['location']['y'])) {
                $n = isset($a['name']) ? $a['name'] : '';
                if ($oaeName && strtolower(trim($n)) !== strtolower($oaeName)) continue;

                append_point($out, array(
                    'name'       => $n,
                    'type'       => isset($a['type']) ? $a['type'] : null,
                    'alert_type' => isset($a['alert_type']) ? $a['alert_type'] : null,
                    'created'    => isset($a['created']) ? $a['created'] : null,
                    'street'     => isset($a['street']) ? $a['street'] : null
                ), $a['location']['x'], $a['location']['y']);
            }
        }
        echo json_encode($out); exit;
    }

    // Formato {jams:[...]}
    if (isset($data['jams']) && is_array($data['jams'])) {
        foreach ($data['jams'] as $a) {
            if (isset($a['point']['geometry']['type']) && $a['point']['geometry']['type']==='Point') {
                $n = isset($a['name']) ? $a['name'] : (isset($a['point']['properties']['name']) ? $a['point']['properties']['name'] : '');
                if ($oaeName && strtolower(trim($n)) !== strtolower($oaeName)) continue;

                $lng = isset($a['point']['geometry']['coordinates'][0]) ? $a['point']['geometry']['coordinates'][0] : null;
                $lat = isset($a['point']['geometry']['coordinates'][1]) ? $a['point']['geometry']['coordinates'][1] : null;

                append_point($out, array(
                    'name'       => $n,
                    'type'       => isset($a['type']) ? $a['type'] : null,
                    'alert_type' => isset($a['alert_type']) ? $a['alert_type'] : 'JAM',
                    'created'    => isset($a['created']) ? $a['created'] : null,
                    'street'     => isset($a['street']) ? $a['street'] : null
                ), $lng, $lat);
            }
        }
        echo json_encode($out); exit;
    }

    // Desconhecido: retorna bruto
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error'=>$e->getMessage()));
}
