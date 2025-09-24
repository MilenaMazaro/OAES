<?php
header('Content-Type: application/json; charset=utf-8');

// 1) Endpoint real que seu oaes.php usa quando mock=0
$endpoint = 'SEU_ENDPOINT_COMPLETO'; // ex: https://api.algumservico.com/v1/alertas

// 2) Se precisar de token no header, coloque aqui:
$headers = array(
    'Authorization: Bearer ' . getenv('MOBFA_TOKEN')
);

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

$resp = curl_exec($ch);
$err  = curl_error($ch);
$st   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode(array(
    'http_status' => $st,
    'curl_error'  => $err,
    'body'        => $resp
));
