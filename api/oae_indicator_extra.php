<?php
require_once __DIR__ . '/config.php';

$path  = OAE_INDICATOR_EXTRAS_FILE;
$items = json_read($path);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $oaeId = isset($_GET['oaeId']) ? $_GET['oaeId'] : null;
    if ($oaeId) {
        $out = array();
        foreach ($items as $i) if (isset($i['oaeId']) && $i['oaeId']===$oaeId) $out[] = $i;
        echo json_encode($out); exit;
    }
    echo json_encode($items); exit;
}

if ($method === 'POST') {
    $b = body_json();
    if (!isset($b['oaeId']) || !isset($b['indicatorId'])) {
        http_response_code(400); echo json_encode(array('error'=>'oaeId & indicatorId required')); exit;
    }
    foreach ($items as $i) {
        if ($i['oaeId']===$b['oaeId'] && $i['indicatorId']===$b['indicatorId']) {
            http_response_code(409); echo json_encode(array('error'=>'exists')); exit;
        }
    }
    $items[] = array(
        'oaeId'       => $b['oaeId'],
        'indicatorId' => $b['indicatorId'],
        'weight'      => isset($b['weight']) ? intval($b['weight']) : null
    );
    json_write($path, $items); echo json_encode(array('ok'=>true)); exit;
}

if ($method === 'DELETE') {
    $oaeId = isset($_GET['oaeId']) ? $_GET['oaeId'] : null;
    $indId = isset($_GET['indicatorId']) ? $_GET['indicatorId'] : null;
    if (!$oaeId || !$indId) { http_response_code(400); echo json_encode(array('error'=>'params required')); exit; }
    $new = array();
    foreach ($items as $i) {
        if (!($i['oaeId']===$oaeId && $i['indicatorId']===$indId)) $new[] = $i;
    }
    json_write($path, $new); echo json_encode(array('ok'=>true)); exit;
}

http_response_code(405); echo json_encode(array('error'=>'Method not allowed'));
