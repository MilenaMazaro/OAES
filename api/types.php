<?php
require_once __DIR__ . '/config.php';

$types = json_read(OAE_TYPES_FILE);
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

header('Content-Type: application/json; charset=utf-8');

if ($method === 'GET') {
    echo json_encode($types);
    exit;
}

if ($method === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    if (!$action) { http_response_code(400); echo json_encode(array('error'=>'Missing action')); exit; }

    if ($action === 'create') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';
        if ($name === '') { http_response_code(400); echo json_encode(array('error'=>'Name required')); exit; }

        // slug ID compatível com PHP 5
        $id = slugify_legacy($name);
        if ($id === '') $id = uniqid('tipo_');

        // verifica duplicado
        $exists = false;
        foreach ($types as $t) {
            if (isset($t['id']) && $t['id'] === $id) { $exists = true; break; }
        }
        if ($exists) { http_response_code(400); echo json_encode(array('error'=>'Type already exists')); exit; }

        $types[] = array('id'=>$id, 'name'=>$name, 'desc'=>$desc);
        json_write(OAE_TYPES_FILE, $types);
        echo json_encode(array('ok'=>true, 'id'=>$id));
        exit;
    }

    if ($action === 'update') {
        $id   = isset($_POST['id']) ? $_POST['id'] : '';
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';

        $found = false;
        for ($i=0; $i<count($types); $i++) {
            if (isset($types[$i]['id']) && $types[$i]['id'] === $id) {
                if ($name !== '') $types[$i]['name'] = $name;
                $types[$i]['desc'] = $desc;
                $found = true;
                break;
            }
        }
        if (!$found) { http_response_code(404); echo json_encode(array('error'=>'Not found')); exit; }

        json_write(OAE_TYPES_FILE, $types);
        echo json_encode(array('ok'=>true));
        exit;
    }

    if ($action === 'delete') {
        $id = isset($_POST['id']) ? $_POST['id'] : '';

        // remove do array sem usar closures
        $new = array();
        $removed = false;
        for ($i=0; $i<count($types); $i++) {
            if (isset($types[$i]['id']) && $types[$i]['id'] === $id) { $removed = true; continue; }
            $new[] = $types[$i];
        }
        if (!$removed) { http_response_code(404); echo json_encode(array('error'=>'Not found')); exit; }

        $types = $new;
        json_write(OAE_TYPES_FILE, $types);

        // limpa vínculos no mapa
        $map = json_read(OAE_TYPE_INDICATORS_FILE);
        if (isset($map[$id])) {
            unset($map[$id]);
            json_write(OAE_TYPE_INDICATORS_FILE, $map);
        }

        echo json_encode(array('ok'=>true));
        exit;
    }

    http_response_code(400);
    echo json_encode(array('error'=>'Invalid action'));
    exit;
}

http_response_code(405);
echo json_encode(array('error'=>'Method not allowed'));
exit;

/* ===== helpers ===== */
function slugify_legacy($text) {
    // tenta normalizar acentos (se iconv disponível)
    if (function_exists('iconv')) {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    }
    $text = strtolower($text);
    $text = preg_replace('~[^a-z0-9]+~', '-', $text);
    $text = trim($text, '-');
    return $text;
}
