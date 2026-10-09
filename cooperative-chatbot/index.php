<?php
require __DIR__.'/bootstrap.php';
session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','samesite'=>'Strict']);
session_start();
$_SESSION['csrf']??=bin2hex(random_bytes(24));
$error='';
if($_SERVER['REQUEST_METHOD']==='POST') {
    if(!hash_equals($_SESSION['csrf'],$_POST['csrf']??'')) { http_response_code(403); exit('Invalid CSRF'); }
    $action=$_POST['action']??'';
    if($action==='login') {
        $user=trim($_POST['username']??'');
        if(password_verify($_POST['password']??'', $config['staff'][$user]??'')) { session_regenerate_id(true); $_SESSION['staff']=$user; header('Location: index.php'); exit; }
        sleep(1); $error='ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    } elseif(isset($_SESSION['staff'])) {
        $uid=$_POST['contact']??''; $staff=$_SESSION['staff'];
        try {
            $db->beginTransaction();
            if($action==='logout') { $db->rollBack(); session_destroy(); header('Location: index.php'); exit; }
            elseif($action==='claim') {
                query("UPDATE contacts SET status='staff',owner=? WHERE id=? AND (owner IS NULL OR owner=?)",[$staff,$uid,$staff]);
                if(!$db->query('SELECT changes()')->fetchColumn()) throw new RuntimeException('เจ้าหน้าที่อื่นรับเรื่องนี้แล้ว');
            } elseif(in_array($action,['reply','close','member'],true)) {
                $contact=query('SELECT * FROM contacts WHERE id=?',[$uid])->fetch(PDO::FETCH_ASSOC);
                if(!$contact || $contact['owner']!==$staff || $contact['status']!=='staff') throw new RuntimeException('กรุณารับเรื่องก่อนดำเนินการ');
                if($action==='reply') {
                    $text=trim($_POST['body']??'');
                    if($text==='' || mb_strlen($text)>5000) throw new RuntimeException('ข้อความต้องยาว 1–5,000 ตัวอักษร');
                    enqueue($uid,$text,null,'staff');
                } elseif($action==='close') query("UPDATE contacts SET status='closed',owner=NULL WHERE id=?",[$uid]);
                else {
                    $verified=isset($_POST['verified'])?1:0;
                    foreach(['deposit','debt'] as $field) if(($_POST[$field]??'')!=='' && (!is_numeric($_POST[$field]) || (float)$_POST[$field]<0)) throw new RuntimeException('ยอดเงินต้องเป็นตัวเลขไม่ติดลบ');
                    if(!$verified && (($_POST['deposit']??'')!=='' || ($_POST['debt']??'')!=='')) throw new RuntimeException('ยืนยันตัวตนก่อนบันทึกยอดเงิน');
                    query('UPDATE contacts SET name=?,member_no=?,verified=?,deposit=?,debt=? WHERE id=?',[mb_substr(trim($_POST['name']??''),0,150),mb_substr(trim($_POST['member_no']??''),0,50),$verified,$_POST['deposit']?:null,$_POST['debt']?:null,$uid]);
                }
            } elseif($action==='faq') {
                $question=trim($_POST['question']??''); $answer=trim($_POST['answer']??''); $words=trim($_POST['keywords']??'');
                if(!$question || !$words || !$answer || mb_strlen($answer)>5000) throw new RuntimeException('กรอกคำถาม คำค้น และคำตอบไม่เกิน 5,000 ตัวอักษร');
                $id=(int)($_POST['faq_id']??0);
                if($id) query('UPDATE faq SET question=?,keywords=?,answer=?,active=? WHERE id=?',[$question,$words,$answer,isset($_POST['active'])?1:0,$id]);
                else query('INSERT INTO faq(question,keywords,answer,active) VALUES(?,?,?,?)',[$question,$words,$answer,isset($_POST['active'])?1:0]);
            } else throw new RuntimeException('ไม่พบคำสั่ง');
            query('INSERT INTO audit(staff,action,contact,created) VALUES(?,?,?,?)',[$staff,$action,$uid,now()]);
            $db->commit(); header('Location: index.php?view='.urlencode($_GET['view']??'inbox').'&contact='.urlencode($uid)); exit;
        } catch(Throwable $e) { if($db->inTransaction()) $db->rollBack(); $error=$e instanceof RuntimeException?$e->getMessage():'ดำเนินการไม่สำเร็จ'; }
    }
}
function csrf(): void { echo '<input type="hidden" name="csrf" value="'.h($_SESSION['csrf']).'">'; }
$labels=['bot'=>'บอตดูแล','waiting'=>'รอเจ้าหน้าที่','staff'=>'กำลังดำเนินการ','closed'=>'ปิดเรื่อง'];
?>
<!doctype html><html lang="th"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>สหกรณ์ • ศูนย์บริการสมาชิก</title><link rel="stylesheet" href="style.css"><body>
<?php if(!isset($_SESSION['staff'])): ?>
<main class="login"><div class="brand">สหกรณ์ / MEMBER CARE</div><h1>ศูนย์บริการสมาชิก</h1><p>เข้าสู่ระบบสำหรับเจ้าหน้าที่สหกรณ์</p>
<?php if(!$config['staff']): ?><div class="notice">ยังไม่ได้ตั้งค่าผู้ใช้ กรุณาทำตาม README.md ก่อนเริ่มใช้งาน</div><?php else: ?>
<form method="post"><?php csrf(); ?><input type="hidden" name="action" value="login"><label>ชื่อผู้ใช้<input name="username" required autocomplete="username"></label><label>รหัสผ่าน<input name="password" type="password" required autocomplete="current-password"></label><button>เข้าสู่ระบบ</button></form><?php endif; ?><p class="error"><?=h($error)?></p></main>
<?php else:
$view=$_GET['view']??'inbox'; $uid=$_GET['contact']??'';
$counts=query('SELECT status,COUNT(*) n FROM contacts GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<aside><div class="brand">สหกรณ์<br><small>MEMBER CARE</small></div><h2>ศูนย์บริการสมาชิก</h2><a href="index.php">กล่องข้อความ</a><a href="?view=faq">คำถามพื้นฐาน</a><a href="?view=settings">การเชื่อมต่อ / รายงาน</a><div class="staff">เจ้าหน้าที่ <?=h($_SESSION['staff'])?><form method="post"><?php csrf(); ?><button class="secondary" name="action" value="logout">ออกจากระบบ</button></form></div></aside>
<main class="app"><header><div><small>LINE OA · งานบริการสหกรณ์</small><h1>ดูแลสมาชิกอย่างเป็นระบบ</h1></div><a class="refresh" href="<?=h($_SERVER['REQUEST_URI'])?>">รีเฟรชข้อมูล</a></header>
<section class="stats"><?php foreach($labels as $key=>$label): ?><article><span><?=h($label)?></span><strong><?=h($counts[$key]??0)?></strong></article><?php endforeach; ?></section>
<?php if($error): ?><p class="error"><?=h($error)?></p><?php endif; ?>
<?php if($view==='faq'): ?><section class="panel"><h2>คำถามและคำตอบพื้นฐาน</h2><p>ใช้คำค้นคั่นด้วยเครื่องหมายจุลภาค หากตรงหลายคำตอบ ระบบจะส่งให้เจ้าหน้าที่ ต้องตรวจข้อมูลสหกรณ์ก่อนเปิดใช้งาน</p>
<?php $faqs=query('SELECT * FROM faq ORDER BY id')->fetchAll(PDO::FETCH_ASSOC); $faqs[]=['id'=>0,'question'=>'','keywords'=>'','answer'=>'','active'=>0]; foreach($faqs as $f): ?>
<form method="post" class="faq"><?php csrf(); ?><input type="hidden" name="action" value="faq"><input type="hidden" name="faq_id" value="<?=h($f['id'])?>"><label>คำถาม<input name="question" value="<?=h($f['question'])?>" placeholder="เช่น เวลาทำการของสหกรณ์" required></label><label>คำค้น<input name="keywords" value="<?=h($f['keywords'])?>" placeholder="เวลาทำการ,เปิดกี่โมง" required></label><label>คำตอบ<textarea name="answer" maxlength="5000" required><?=h($f['answer'])?></textarea></label><label class="check"><input type="checkbox" name="active" <?=$f['active']?'checked':''?>> ตรวจสอบแล้ว / เปิดใช้บอตตอบ</label><button><?= $f['id']?'บันทึกการแก้ไข':'เพิ่มคำถาม' ?></button></form><?php endforeach; ?></section>
<?php elseif($view==='settings'): ?><section class="panel"><h2>สถานะการเชื่อมต่อ</h2><p>Channel secret: <?=$config['line_secret']?'ตั้งค่าแล้ว':'ยังไม่ได้ตั้งค่า'?> · Access token: <?=$config['line_token']?'ตั้งค่าแล้ว':'ยังไม่ได้ตั้งค่า'?></p><p>Webhook: <code>https://โดเมนของคุณ/pdhnutrition/cooperative-chatbot/webhook.php</code></p><p>สมาชิกที่ติดต่อแล้ว <?=array_sum($counts)?> คน / เป้าหมาย 3,000 คน</p><p>ข้อมูลสมาชิกและยอดเงินในรุ่นนี้บันทึกโดยเจ้าหน้าที่ ยังไม่ได้เชื่อมฐานข้อมูลสหกรณ์</p><h3>คิวส่งข้อความ</h3><?php foreach(query('SELECT state,COUNT(*) n FROM outbox GROUP BY state') as $s): ?><p><?=h($s['state'])?>: <?=h($s['n'])?></p><?php endforeach; ?><h3>ข้อความส่งไม่สำเร็จล่าสุด</h3><?php foreach(query("SELECT id,last_error FROM outbox WHERE state='failed' ORDER BY id DESC LIMIT 10") as $s): ?><p>#<?=h($s['id'])?> — <?=h($s['last_error'])?></p><?php endforeach; ?><h3>ประวัติการทำงานล่าสุด</h3><?php foreach(query('SELECT * FROM audit ORDER BY id DESC LIMIT 20') as $a): ?><p><?=h($a['created'].' · '.$a['staff'].' · '.$a['action'])?></p><?php endforeach; ?></section>
<?php else:
$search=trim($_GET['search']??'');
$contacts=query("SELECT * FROM contacts WHERE name LIKE ? OR member_no LIKE ? ORDER BY CASE status WHEN 'waiting' THEN 0 WHEN 'staff' THEN 1 ELSE 2 END,updated DESC LIMIT 100",['%'.$search.'%','%'.$search.'%'])->fetchAll(PDO::FETCH_ASSOC);
$c=$uid?query('SELECT * FROM contacts WHERE id=?',[$uid])->fetch(PDO::FETCH_ASSOC):false;
?>
<section class="inbox"><div class="panel contact-list"><h2>ข้อความสมาชิก</h2><form method="get"><input name="search" value="<?=h($search)?>" placeholder="ค้นชื่อ / เลขสมาชิก"><button class="secondary">ค้นหา</button></form>
<?php foreach($contacts as $row): ?><a class="contact <?=$uid===$row['id']?'selected':''?>" href="?contact=<?=urlencode($row['id'])?>"><b><?=h($row['name'])?></b><span class="badge <?=h($row['status'])?>"><?=h($labels[$row['status']])?></span><small><?=h($row['updated'])?> · <?=h($row['owner']??'ยังไม่มีผู้รับเรื่อง')?></small></a><?php endforeach; ?><?php if(!$contacts): ?><p class="empty">ยังไม่มีข้อความจากสมาชิก<br>ตั้งค่า LINE OA เพื่อเริ่มรับข้อความ</p><?php endif; ?></div>
<div class="panel conversation"><?php if(!$c): ?><div class="empty"><h2>เลือกแชตเพื่อเริ่มดูแลสมาชิก</h2><p>ข้อความจาก LINE จะแสดงที่นี่ พร้อมประวัติและสถานะการรับเรื่อง</p></div><?php else: ?>
<h2><?=h($c['name'])?></h2><p><?=h($labels[$c['status']])?> · ผู้ดูแล <?=h($c['owner']??'ยังไม่มี')?></p><form method="post"><?php csrf(); ?><input type="hidden" name="contact" value="<?=h($uid)?>"><button name="action" value="claim">รับเรื่อง / เปิดเรื่องอีกครั้ง</button><?php if($c['owner']===$_SESSION['staff']): ?> <button class="secondary" name="action" value="close">ปิดเรื่อง</button><?php endif; ?></form>
<div class="messages"><?php foreach(query('SELECT m.*,o.state delivery FROM messages m LEFT JOIN outbox o ON o.message_id=m.id WHERE m.contact=? ORDER BY m.id DESC LIMIT 200',[$uid])->fetchAll(PDO::FETCH_ASSOC) as $m): ?><article class="bubble <?=h($m['direction'])?>"><small><?=h(['member'=>'สมาชิก','bot'=>'บอต','staff'=>'เจ้าหน้าที่'][$m['direction']])?> · <?=h($m['created'])?> <?=h($m['delivery']??'')?></small><p><?=nl2br(h($m['body']))?></p></article><?php endforeach; ?></div>
<?php if($c['owner']===$_SESSION['staff'] && $c['status']==='staff'): ?><form method="post"><?php csrf(); ?><input type="hidden" name="action" value="reply"><input type="hidden" name="contact" value="<?=h($uid)?>"><textarea name="body" placeholder="พิมพ์คำตอบถึงสมาชิก" maxlength="5000" required></textarea><button>ส่งข้อความผ่าน LINE</button></form>
<details><summary>ข้อมูลสมาชิก / ยืนยันโดยเจ้าหน้าที่</summary><p>ตรวจสอบตัวตนจากทะเบียนสหกรณ์ก่อนผูกบัญชี LINE ยอดเงินเป็นข้อมูลที่เจ้าหน้าที่บันทึก ไม่ใช่ยอดจากระบบบัญชี</p><form method="post"><?php csrf(); ?><input type="hidden" name="action" value="member"><input type="hidden" name="contact" value="<?=h($uid)?>"><?php foreach(['name'=>'ชื่อสมาชิก','member_no'=>'เลขสมาชิก','deposit'=>'ยอดเงินฝาก (บาท)','debt'=>'ยอดหนี้ (บาท)'] as $field=>$label): ?><label><?=h($label)?><input name="<?=h($field)?>" value="<?=h($c[$field])?>"></label><?php endforeach; ?><label class="check"><input type="checkbox" name="verified" <?=$c['verified']?'checked':''?>> ตรวจสอบตัวตนและการผูก LINE แล้ว</label><button>บันทึกข้อมูลสมาชิก</button></form></details><?php endif; ?>
<p>เลขสมาชิก: <?=h($c['member_no']?:'ยังไม่ได้ผูกสมาชิก')?> · <?= $c['verified']?'ยืนยันตัวตนแล้ว':'ยังไม่ยืนยันตัวตน' ?></p><?php if($c['verified']): ?><p>เงินฝาก <?=h($c['deposit']??'ยังไม่มีข้อมูล')?> บาท · หนี้ <?=h($c['debt']??'ยังไม่มีข้อมูล')?> บาท</p><?php endif; ?>
<?php endif; ?></div></section><?php endif; ?></main><?php endif; ?></body></html>
