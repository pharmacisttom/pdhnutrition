<?php
if(PHP_SAPI!=='cli') { http_response_code(404); exit; }
$path=__DIR__.'/config.local.php';
if(is_file($path)) { fwrite(STDERR,"Configuration exists. Edit staff hashes in config.local.php to add staff.\n"); exit(1); }
echo "Staff username: "; $user=trim(fgets(STDIN));
echo "Password (at least 12 characters; terminal input is visible): "; $password=trim(fgets(STDIN));
if(!$user || strlen($password)<12) { fwrite(STDERR,"Invalid username or short password.\n"); exit(1); }
$config=['staff'=>[$user=>password_hash($password,PASSWORD_DEFAULT)],'line_secret'=>'','line_token'=>''];
file_put_contents($path,"<?php\nreturn ".var_export($config,true).";\n",LOCK_EX);
echo "Created config.local.php. Configure LINE credentials there.\n";
