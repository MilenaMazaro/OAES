<?php
require_once __DIR__ . '/config.php';

// Indicators
$ind = array(
    array('id'=>'FLOW_AVG_SPEED','label'=>'Velocidade Média','unit'=>'km/h','source'=>'FLOW'),
    array('id'=>'FLOW_TRAVEL_TIME','label'=>'Tempo Médio de Travessia','unit'=>'min','source'=>'FLOW'),
    array('id'=>'FLOW_SLOW_EVENTS','label'=>'Eventos de Lentidão','unit'=>'count','source'=>'FLOW'),
    array('id'=>'FLOW_SLOW_DURATION','label'=>'Duração Média da Lentidão','unit'=>'min','source'=>'FLOW'),
    array('id'=>'FLOW_CONGESTION_INDEX','label'=>'Índice de Congestionamento','unit'=>'%','source'=>'FLOW'),
    array('id'=>'INC_ACCIDENTS','label'=>'Acidentes','unit'=>'count','source'=>'INCIDENT'),
    array('id'=>'INC_STOPPED_LANE','label'=>'Parados na Via','unit'=>'count','source'=>'INCIDENT'),
    array('id'=>'INC_STOPPED_SHOULDER','label'=>'Parados no Acostamento','unit'=>'count','source'=>'INCIDENT'),
    array('id'=>'INC_POTHOLES','label'=>'Buracos','unit'=>'count','source'=>'INCIDENT'),
    array('id'=>'INC_FLOOD','label'=>'Alagamentos','unit'=>'count','source'=>'INCIDENT'),
    array('id'=>'INC_OTHER_HAZARDS','label'=>'Outros Perigos','unit'=>'count','source'=>'INCIDENT'),
    array('id'=>'ASSET_CAM_COUNT','label'=>'Câmeras Associadas','unit'=>'count','source'=>'ASSET'),
    array('id'=>'ASSET_CAM_UPTIME','label'=>'Câmeras Online','unit'=>'%','source'=>'ASSET')
);
json_write(INDICATORS_FILE, $ind);

// Types
$types = array(
    array('id'=>'ponte','name'=>"Ponte",'description'=>"Estrutura que transpõe cursos d'água."),
    array('id'=>'viaduto','name'=>"Viaduto",'description'=>"Estrutura que transpõe outras vias, vales ou áreas urbanas."),
    array('id'=>'tunel','name'=>"Túnel",'description'=>"Passagem subterrânea para veículos."),
    array('id'=>'passarela','name'=>"Passarela",'description'=>"Estrutura elevada para pedestres."),
    array('id'=>'trincheira','name'=>"Trincheira / Passagem Inferior",'description'=>"Via que passa por baixo de outra.")
);
json_write(OAE_TYPES_FILE, $types);

// Links Type→Indicators
$links = array(
    array('oaeTypeId'=>'ponte','indicatorId'=>'FLOW_AVG_SPEED','weight'=>1),
    array('oaeTypeId'=>'ponte','indicatorId'=>'FLOW_TRAVEL_TIME','weight'=>2),
    array('oaeTypeId'=>'ponte','indicatorId'=>'FLOW_CONGESTION_INDEX','weight'=>3),
    array('oaeTypeId'=>'ponte','indicatorId'=>'INC_ACCIDENTS','weight'=>4),
    array('oaeTypeId'=>'ponte','indicatorId'=>'INC_POTHOLES','weight'=>5),
    array('oaeTypeId'=>'ponte','indicatorId'=>'ASSET_CAM_COUNT','weight'=>6),
    array('oaeTypeId'=>'ponte','indicatorId'=>'ASSET_CAM_UPTIME','weight'=>7),

    array('oaeTypeId'=>'viaduto','indicatorId'=>'FLOW_AVG_SPEED','weight'=>1),
    array('oaeTypeId'=>'viaduto','indicatorId'=>'FLOW_SLOW_EVENTS','weight'=>2),
    array('oaeTypeId'=>'viaduto','indicatorId'=>'FLOW_SLOW_DURATION','weight'=>3),
    array('oaeTypeId'=>'viaduto','indicatorId'=>'INC_ACCIDENTS','weight'=>4),
    array('oaeTypeId'=>'viaduto','indicatorId'=>'INC_OTHER_HAZARDS','weight'=>5),
    array('oaeTypeId'=>'viaduto','indicatorId'=>'ASSET_CAM_COUNT','weight'=>6),
    array('oaeTypeId'=>'viaduto','indicatorId'=>'ASSET_CAM_UPTIME','weight'=>7),

    array('oaeTypeId'=>'tunel','indicatorId'=>'FLOW_TRAVEL_TIME','weight'=>1),
    array('oaeTypeId'=>'tunel','indicatorId'=>'FLOW_CONGESTION_INDEX','weight'=>2),
    array('oaeTypeId'=>'tunel','indicatorId'=>'INC_ACCIDENTS','weight'=>3),
    array('oaeTypeId'=>'tunel','indicatorId'=>'INC_FLOOD','weight'=>4),
    array('oaeTypeId'=>'tunel','indicatorId'=>'ASSET_CAM_COUNT','weight'=>5),
    array('oaeTypeId'=>'tunel','indicatorId'=>'ASSET_CAM_UPTIME','weight'=>6),

    array('oaeTypeId'=>'passarela','indicatorId'=>'FLOW_TRAVEL_TIME','weight'=>1),
    array('oaeTypeId'=>'passarela','indicatorId'=>'FLOW_SLOW_EVENTS','weight'=>2),
    array('oaeTypeId'=>'passarela','indicatorId'=>'INC_OTHER_HAZARDS','weight'=>3),
    array('oaeTypeId'=>'passarela','indicatorId'=>'ASSET_CAM_COUNT','weight'=>4),
    array('oaeTypeId'=>'passarela','indicatorId'=>'ASSET_CAM_UPTIME','weight'=>5),

    array('oaeTypeId'=>'trincheira','indicatorId'=>'FLOW_TRAVEL_TIME','weight'=>1),
    array('oaeTypeId'=>'trincheira','indicatorId'=>'FLOW_CONGESTION_INDEX','weight'=>2),
    array('oaeTypeId'=>'trincheira','indicatorId'=>'INC_FLOOD','weight'=>3),
    array('oaeTypeId'=>'trincheira','indicatorId'=>'INC_POTHOLES','weight'=>4),
    array('oaeTypeId'=>'trincheira','indicatorId'=>'ASSET_CAM_COUNT','weight'=>5),
    array('oaeTypeId'=>'trincheira','indicatorId'=>'ASSET_CAM_UPTIME','weight'=>6)
);
json_write(OAE_TYPE_INDICATORS_FILE, $links);

// Extras vazio
json_write(OAE_INDICATOR_EXTRAS_FILE, array());

echo json_encode(array('ok'=>true));
