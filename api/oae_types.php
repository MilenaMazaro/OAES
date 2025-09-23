<?php
require_once __DIR__ . '/config.php';

$path  = OAE_TYPES_FILE;
$types = json_read($path);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode($types);
    exit;
}

if ($method === 'POST') {
    $b = body_json();
    if (!isset($b['name']) || $b['name'] === '') {
        http_response_code(400);
        echo json_encode(array('error'=>'name required'));
        exit;
    }
    $id = isset($b['id']) ? $b['id'] : strtolower(preg_replace('/\s+/', '-', @iconv('UTF-8','ASCII//TRANSLIT',$b['name'])));
    foreach ($types as $t) {
        if (isset($t['id']) && $t['id'] === $id) {
            http_response_code(409);
            echo json_encode(array('error'=>'exists'));
            exit;
        }
    }
    $types[] = array(
        'id' => $id,
        'name' => $b['name'],
        'description' => isset($b['description']) ? $b['description'] : ''
    );
    json_write($path, $types);
    echo json_encode(array('ok'=>true,'id'=>$id));
    exit;
}

if ($method === 'PUT') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) { http_response_code(400); echo json_encode(array('error'=>'id required')); exit; }
    $b = body_json();
    for ($i=0; $i<count($types); $i++) {
        if (isset($types[$i]['id']) && $types[$i]['id'] === $id) {
            if (isset($b['name'])) $types[$i]['name'] = $b['name'];
            if (isset($b['description'])) $types[$i]['description'] = $b['description'];
            json_write($path, $types);
            echo json_encode(array('ok'=>true));
            exit;
        }
    }
    http_response_code(404); echo json_encode(array('error'=>'not found')); exit;
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) { http_response_code(400); echo json_encode(array('error'=>'id required')); exit; }
    $new = array();
    foreach ($types as $t) {
        if (!(isset($t['id']) && $t['id'] === $id)) $new[] = $t;
    }
    json_write($path, $new);
    echo json_encode(array('ok'=>true));
    exit;
}

http_response_code(405);
echo json_encode(array('error'=>'Method not allowed'));
