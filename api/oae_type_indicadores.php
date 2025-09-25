<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$path  = OAE_TYPE_INDICATORS_FILE;
$links = json_read($path);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $typeId = isset($_GET['oaeTypeId']) ? $_GET['oaeTypeId'] : null;
    if ($typeId) {
        $items = array();
        foreach ($links as $l) {
            if (isset($l['oaeTypeId']) && $l['oaeTypeId'] === $typeId) $items[] = $l;
        }
        // ordenar por weight (nulls ao final)
        usort($items, 'cmp_weight');
        echo json_encode($items);
        exit;
    }
    echo json_encode($links);
    exit;
}

if ($method === 'POST') {
    $b = body_json();
    if (!isset($b['oaeTypeId']) || !isset($b['indicatorId'])) {
        http_response_code(400); echo json_encode(array('error'=>'oaeTypeId & indicatorId required')); exit;
    }
    foreach ($links as $l) {
        if (isset($l['oaeTypeId'],$l['indicatorId']) && $l['oaeTypeId']===$b['oaeTypeId'] && $l['indicatorId']===$b['indicatorId']) {
            http_response_code(409); echo json_encode(array('error'=>'exists')); exit;
        }
    }
    $links[] = array(
        'oaeTypeId'   => $b['oaeTypeId'],
        'indicatorId' => $b['indicatorId'],
        'weight'      => isset($b['weight']) ? intval($b['weight']) : null
    );
    json_write($path, $links);
    echo json_encode(array('ok'=>true));
    exit;
}

if ($method === 'PUT') {
    $typeId = isset($_GET['oaeTypeId']) ? $_GET['oaeTypeId'] : null;
    $indId  = isset($_GET['indicatorId']) ? $_GET['indicatorId'] : null;
    if (!$typeId || !$indId) { http_response_code(400); echo json_encode(array('error'=>'params required')); exit; }
    $b = body_json();
    for ($i=0; $i<count($links); $i++) {
        if ($links[$i]['oaeTypeId']===$typeId && $links[$i]['indicatorId']===$indId) {
            if (isset($b['weight'])) $links[$i]['weight'] = intval($b['weight']);
            json_write($path, $links);
            echo json_encode(array('ok'=>true));
            exit;
        }
    }
    http_response_code(404); echo json_encode(array('error'=>'not found')); exit;
}

if ($method === 'DELETE') {
    $typeId = isset($_GET['oaeTypeId']) ? $_GET['oaeTypeId'] : null;
    $indId  = isset($_GET['indicatorId']) ? $_GET['indicatorId'] : null;
    if (!$typeId || !$indId) { http_response_code(400); echo json_encode(array('error'=>'params required')); exit; }
    $new = array();
    for ($i=0; $i<count($links); $i++) {
        if (!($links[$i]['oaeTypeId']===$typeId && $links[$i]['indicatorId']===$indId)) $new[] = $links[$i];
    }
    json_write($path, $new);
    echo json_encode(array('ok'=>true));
    exit;
}

http_response_code(405);
echo json_encode(array('error'=>'Method not allowed'));

function cmp_weight($a, $b) {
    $wa = isset($a['weight']) ? intval($a['weight']) : 999;
    $wb = isset($b['weight']) ? intval($b['weight']) : 999;
    if ($wa == $wb) return 0;
    return ($wa < $wb) ? -1 : 1;
}
