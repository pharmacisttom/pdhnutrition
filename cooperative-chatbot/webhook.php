<?php
require __DIR__.'/bootstrap.php';
if ($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(405); exit; }
$body=file_get_contents('php://input');
if (!$config['line_secret']) { http_response_code(503); exit; }
$signature=base64_encode(hash_hmac('sha256',$body,$config['line_secret'],true));
if (!hash_equals($signature,$_SERVER['HTTP_X_LINE_SIGNATURE']??'')) { http_response_code(401); exit; }
try {
    $data=json_decode($body,true,512,JSON_THROW_ON_ERROR);
    if (!isset($data['events']) || !is_array($data['events'])) { http_response_code(400); exit; }
    foreach($data['events'] as $event) receiveEvent($event);
    header('Content-Type: application/json'); echo '{"ok":true}';
} catch (JsonException $e) { http_response_code(400); }
catch (Throwable $e) { error_log('Cooperative webhook failed'); http_response_code(500); }
