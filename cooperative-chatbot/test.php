<?php
if(PHP_SAPI!=='cli') exit;
putenv('COOP_DB=:memory:');
require __DIR__.'/bootstrap.php';
function check(bool $condition,string $label): void { if(!$condition) throw new RuntimeException($label); echo "PASS $label\n"; }
query('INSERT INTO faq(question,keywords,answer,active) VALUES(?,?,?,1)',['เวลาทำการ','เปิดกี่โมง','ข้อมูลที่อนุมัติ']);
$event=['webhookEventId'=>'event-1','type'=>'message','source'=>['type'=>'user','userId'=>'U-test'],'replyToken'=>'test-token','message'=>['type'=>'text','id'=>'msg-1','text'=>'เปิดกี่โมง']];
receiveEvent($event); receiveEvent($event);
check((int)query('SELECT COUNT(*) FROM messages')->fetchColumn()===2,'duplicate webhook stores and replies only once');
check(query('SELECT status FROM contacts')->fetchColumn()==='bot','approved FAQ retains bot mode');
$event['webhookEventId']='event-2'; $event['message']['id']='msg-2'; $event['message']['text']='ติดต่อเจ้าหน้าที่'; receiveEvent($event);
check(query('SELECT status FROM contacts')->fetchColumn()==='waiting','handoff enters waiting queue');
$n=(int)query('SELECT COUNT(*) FROM outbox')->fetchColumn();
$event['webhookEventId']='event-3'; receiveEvent($event);
check((int)query('SELECT COUNT(*) FROM outbox')->fetchColumn()===$n,'bot stays silent during handoff');
query("UPDATE contacts SET status='staff',owner='alice'");
query("UPDATE contacts SET owner='bob' WHERE id='U-test' AND (owner IS NULL OR owner='bob')");
check(query('SELECT owner FROM contacts')->fetchColumn()==='alice','second staff cannot take assigned conversation');
receiveEvent(['webhookEventId'=>'unsend-1','type'=>'unsend','source'=>['type'=>'user','userId'=>'U-test'],'unsend'=>['messageId'=>'msg-1']]);
check(query("SELECT body FROM messages WHERE line_id='msg-1'")->fetchColumn()==='[สมาชิกยกเลิกข้อความ]','unsent text removed');
$event['source']['type']='group'; $event['webhookEventId']='group-1'; receiveEvent($event);
check((int)query('SELECT COUNT(*) FROM contacts')->fetchColumn()===1,'group messages ignored');
query("UPDATE contacts SET status='bot',owner=NULL");
query("INSERT INTO faq(question,keywords,answer,active) VALUES('other','เปิดกี่โมง','other',1)");
$event['source']['type']='user'; $event['webhookEventId']='ambiguous-1'; $event['message']['text']='เปิดกี่โมง'; receiveEvent($event);
check(query('SELECT status FROM contacts')->fetchColumn()==='waiting','ambiguous FAQ escalates');
echo "All checks passed. No LINE messages were sent.\n";
