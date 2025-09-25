<?php
function load_json($path) {
    if (!file_exists($path)) return [];
    $txt = file_get_contents($path);
    $data = json_decode($txt, true);
    return $data ?: [];
}
function save_json($path, $data) {
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}
function ok($data){ header('Content-Type: application/json'); echo json_encode(['ok'=>true,'data'=>$data]); exit; }
function err($msg, $code=400){ http_response_code($code); header('Content-Type: application/json'); echo json_encode(['ok'=>false,'error'=>$msg]); exit; }
function body() { return json_decode(file_get_contents('php://input'), true) ?: []; }
