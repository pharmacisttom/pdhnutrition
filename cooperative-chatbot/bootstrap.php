<?php
declare(strict_types=1);
date_default_timezone_set('Asia/Bangkok');
$config = is_file(__DIR__.'/config.local.php') ? require __DIR__.'/config.local.php' : ['staff'=>[], 'line_secret'=>'', 'line_token'=>''];
$db = new PDO('sqlite:'.(getenv('COOP_DB') ?: __DIR__.'/storage/chat.sqlite'));
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA journal_mode=WAL; PRAGMA busy_timeout=5000; PRAGMA foreign_keys=ON;');
$db->exec("CREATE TABLE IF NOT EXISTS contacts (id TEXT PRIMARY KEY, name TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'bot', owner TEXT, member_no TEXT, verified INTEGER NOT NULL DEFAULT 0, deposit TEXT, debt TEXT, updated TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY, contact TEXT NOT NULL REFERENCES contacts(id), direction TEXT NOT NULL, body TEXT NOT NULL, line_id TEXT, created TEXT NOT NULL);
CREATE INDEX IF NOT EXISTS msg_contact ON messages(contact,id);
CREATE TABLE IF NOT EXISTS events (id TEXT PRIMARY KEY);
CREATE TABLE IF NOT EXISTS faq (id INTEGER PRIMARY KEY, question TEXT NOT NULL, keywords TEXT NOT NULL, answer TEXT NOT NULL, active INTEGER NOT NULL DEFAULT 0);
CREATE TABLE IF NOT EXISTS outbox (id INTEGER PRIMARY KEY, contact TEXT NOT NULL, message_id INTEGER NOT NULL, payload TEXT NOT NULL, state TEXT NOT NULL DEFAULT 'pending', attempts INTEGER NOT NULL DEFAULT 0, last_error TEXT, retry_key TEXT NOT NULL);
CREATE TABLE IF NOT EXISTS audit (id INTEGER PRIMARY KEY, staff TEXT NOT NULL, action TEXT NOT NULL, contact TEXT, created TEXT NOT NULL);");
function query(string $sql, array $args=[]): PDOStatement { global $db; $q=$db->prepare($sql); $q->execute($args); return $q; }
function h($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function now(): string { return date('Y-m-d H:i:s'); }
function recordMessage(string $contact,string $direction,string $body,?string $lineId=null): int { global $db; query('INSERT INTO messages(contact,direction,body,line_id,created) VALUES(?,?,?,?,?)',[$contact,$direction,$body,$lineId,now()]); return (int)$db->lastInsertId(); }
function enqueue(string $contact,string $text,?string $replyToken,string $direction): void {
    $id=recordMessage($contact,$direction,$text);
    $payload=['messages'=>[['type'=>'text','text'=>$text]]];
    if ($replyToken) $payload['replyToken']=$replyToken; else $payload['to']=$contact;
    $hex=bin2hex(random_bytes(16)); $uuid=substr($hex,0,8).'-'.substr($hex,8,4).'-4'.substr($hex,13,3).'-a'.substr($hex,17,3).'-'.substr($hex,20);
    query('INSERT INTO outbox(contact,message_id,payload,retry_key) VALUES(?,?,?,?)',[$contact,$id,json_encode($payload,JSON_UNESCAPED_UNICODE),$uuid]);
}
function receiveEvent(array $e): void {
    global $db;
    if (($e['source']['type']??'')!=='user' || empty($e['source']['userId']) || empty($e['webhookEventId'])) return;
    $db->beginTransaction();
    try {
        query('INSERT OR IGNORE INTO events(id) VALUES(?)',[$e['webhookEventId']]);
        if ((int)$db->query('SELECT changes()')->fetchColumn()===0) { $db->commit(); return; }
        $uid=$e['source']['userId'];
        query('INSERT OR IGNORE INTO contacts(id,name,updated) VALUES(?,?,?)',[$uid,'สมาชิก LINE '.substr($uid,-6),now()]);
        if (($e['type']??'')==='unsend') {
            query('UPDATE messages SET body=? WHERE contact=? AND line_id=?',['[สมาชิกยกเลิกข้อความ]',$uid,$e['unsend']['messageId']??'']);
            $db->commit(); return;
        }
        if (($e['type']??'')!=='message') { $db->commit(); return; }
        $text=($e['message']['type']??'')==='text' ? (string)$e['message']['text'] : '[ข้อความประเภท '.($e['message']['type']??'อื่น ๆ').']';
        recordMessage($uid,'member',$text,$e['message']['id']??null);
        query('UPDATE contacts SET updated=? WHERE id=?',[now(),$uid]);
        $status=query('SELECT status FROM contacts WHERE id=?',[$uid])->fetchColumn();
        if (in_array($status,['waiting','staff'],true)) { $db->commit(); return; }
        $answer=null;
        if (mb_strpos($text,'เจ้าหน้าที่')===false && ($e['message']['type']??'')==='text') {
            $matches=[];
            foreach(query('SELECT * FROM faq WHERE active=1')->fetchAll(PDO::FETCH_ASSOC) as $f) {
                foreach(explode(',',$f['keywords']) as $word) {
                    $word=trim($word);
                    if ($word!=='' && mb_stripos($text,$word)!==false) { $matches[$f['id']]=$f['answer']; break; }
                }
            }
            if (count($matches)===1) $answer=reset($matches);
        }
        if ($answer===null) {
            query("UPDATE contacts SET status='waiting',owner=NULL WHERE id=?",[$uid]);
            $answer='รับเรื่องแล้วค่ะ เจ้าหน้าที่สหกรณ์จะตอบกลับในแชตนี้ กรุณารอการติดต่อ และอย่าส่งรหัสผ่านหรือ OTP';
        }
        enqueue($uid,$answer,$e['replyToken']??null,'bot');
        $db->commit();
    } catch(Throwable $ex) { if($db->inTransaction()) $db->rollBack(); throw $ex; }
}
