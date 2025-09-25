<?php
require_once __DIR__ . '/config.php';

$map = json_read(OAE_TYPE_INDICATORS_FILE);
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

header('Content-Type: application/json; charset=utf-8');

if ($method === 'GET') {
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    if ($type) {
        echo json_encode(isset($map[$type]) ? $map[$type] : array());
    } else {
        echo json_encode($map);
    }
    exit;
}

if ($method === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    if ($action !== 'set') { http_response_code(400); echo json_encode(array('error'=>'Invalid action')); exit; }

    $type = isset($_POST['type']) ? $_POST['type'] : null;
    $indicatorsRaw = isset($_POST['indicators']) ? $_POST['indicators'] : null;

    if (!$type || $indicatorsRaw === null) { http_response_code(400); echo json_encode(array('error'=>'type/indicators required')); exit; }

    $ids = json_decode($indicatorsRaw, true);
    if (!is_array($ids)) { http_response_code(400); echo json_encode(array('error'=>'indicators must be array')); exit; }

    // normaliza (únicos e não vazios)
    $clean = array();
    for ($i=0; $i<count($ids); $i++) {
        $v = trim((string)$ids[$i]);
        if ($v === '') continue;
        if (!in_array($v, $clean)) $clean[] = $v;
    }

    $map[$type] = array_values($clean);
    json_write(OAE_TYPE_INDICATORS_FILE, $map);

    echo json_encode(array('ok'=>true));
    exit;
}

http_response_code(405);
echo json_encode(array('error'=>'Method not allowed'));
exit;

