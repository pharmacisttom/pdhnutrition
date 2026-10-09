<?php
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
require __DIR__.'/bootstrap.php';
if (!$config['line_token']) { fwrite(STDERR,"Configure LINE token first.\n"); exit(1); }
$lock=fopen(__DIR__.'/storage/worker.lock','c');
if (!flock($lock,LOCK_EX|LOCK_NB)) exit;
// A crashed push job can retry with the same LINE retry key. Reply jobs are not retried blindly.
query("UPDATE outbox SET state='pending' WHERE state='sending' AND payload NOT LIKE '%replyToken%'");
query("UPDATE outbox SET state='failed',last_error='Interrupted reply: inspect delivery before retry' WHERE state='sending'");
do {
    $jobs=query("SELECT * FROM outbox WHERE state='pending' ORDER BY id LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    foreach($jobs as $job) {
        query("UPDATE outbox SET state='sending',attempts=attempts+1 WHERE id=?",[$job['id']]);
        $reply=isset(json_decode($job['payload'],true)['replyToken']);
        $headers=['Content-Type: application/json','Authorization: Bearer '.$config['line_token']];
        if(!$reply) $headers[]='X-Line-Retry-Key: '.$job['retry_key'];
        $curl=curl_init('https://api.line.me/v2/bot/message/'.($reply?'reply':'push'));
        curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$job['payload'],CURLOPT_HTTPHEADER=>$headers,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>15]);
        curl_exec($curl); $code=(int)curl_getinfo($curl,CURLINFO_HTTP_CODE); curl_close($curl);
        $ok=($code>=200 && $code<300) || (!$reply && $code===409);
        $retry=!$reply && ($code===0 || $code===429 || $code>=500) && (int)$job['attempts']<4;
        query('UPDATE outbox SET state=?,last_error=? WHERE id=?',[$ok?'sent':($retry?'pending':'failed'),$ok?null:'LINE HTTP '.$code,$job['id']]);
    }
    if(in_array('--once',$argv,true)) break;
    sleep(2);
} while(true);
