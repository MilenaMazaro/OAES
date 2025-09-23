<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    echo json_encode(json_read(INDICATORS_FILE));
    exit;
}

http_response_code(405);
echo json_encode(array('error'=>'Method not allowed'));
