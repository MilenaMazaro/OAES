<?php
require_once __DIR__ . '/config.php';

$oaesPath  = MOCK_FILE;
$linksPath = OAE_TYPE_INDICATORS_FILE;
$indPath   = INDICATORS_FILE;
$extraPath = OAE_INDICATOR_EXTRAS_FILE;

$oaeId = isset($_GET['oaeId']) ? $_GET['oaeId'] : null;
if (!$oaeId) { http_response_code(400); echo json_encode(array('error'=>'oaeId required')); exit; }

$oaes = json_read($oaesPath);
$oae  = null;

// Procurar OAE (aceita chaves "id" ou "oaeId")
for ($i=0; $i<count($oaes); $i++) {
    $candidateId = null;
    if (isset($oaes[$i]['id'])) $candidateId = $oaes[$i]['id'];
    else if (isset($oaes[$i]['oaeId'])) $candidateId = $oaes[$i]['oaeId'];
    if ($candidateId === $oaeId) { $oae = $oaes[$i]; break; }
}
if (!$oae) { http_response_code(404); echo json_encode(array('error'=>'OAE not found')); exit; }

$typeId = isset($oae['typeId']) ? $oae['typeId'] : null;
$links  = json_read($linksPath);
$inds   = json_read($indPath);
$extras = json_read($extraPath);

// dicionário de indicadores
$dictInd = array();
for ($i=0; $i<count($inds); $i++) {
    $id = isset($inds[$i]['id']) ? $inds[$i]['id'] : null;
    if ($id) $dictInd[$id] = $inds[$i];
}

$mergedMap = array();

// do tipo
for ($i=0; $i<count($links); $i++) {
    $l = $links[$i];
    if (isset($l['oaeTypeId']) && $l['oaeTypeId'] === $typeId) {
        $iid = $l['indicatorId'];
        $mergedMap[$iid] = array(
            'indicator' => isset($dictInd[$iid]) ? $dictInd[$iid] : array('id'=>$iid,'label'=>$iid),
            'weight'    => isset($l['weight']) ? intval($l['weight']) : 999,
            'isExtra'   => false
        );
    }
}

// extras (sobrescreve)
for ($i=0; $i<count($extras); $i++) {
    $e = $extras[$i];
    if (isset($e['oaeId']) && $e['oaeId'] === $oaeId) {
        $iid = $e['indicatorId'];
        $mergedMap[$iid] = array(
            'indicator' => isset($dictInd[$iid]) ? $dictInd[$iid] : array('id'=>$iid,'label'=>$iid),
            'weight'    => isset($e['weight']) ? intval($e['weight']) : 0,
            'isExtra'   => true
        );
    }
}

// converter para lista + ordenar por weight
$merged = array_values($mergedMap);
usort($merged, 'cmp_weight'); // mesma função do arquivo anterior

echo json_encode($merged);

function cmp_weight($a, $b) {
    $wa = isset($a['weight']) ? intval($a['weight']) : 999;
    $wb = isset($b['weight']) ? intval($b['weight']) : 999;
    if ($wa == $wb) return 0;
    return ($wa < $wb) ? -1 : 1;
}
