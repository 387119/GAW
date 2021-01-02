<?php
include dirname(__FILE__)."/../lib/gaw.php";
/*
 * основная задача этого скрипта, создавать новые логины, и делать это так чтоб на той стороне не заметили подвоха
 */

if (count($argv)<3){
	echo "usage php register_new.php <count> <server_id> [-c/n -s]
		-c - use current free logins and passwords
		-s = use not standart name convention G00000000S00";
	die();
}
$new_count=$argv[1];
$server_id=$argv[2];
$current=$argv[3];
$namec=$argv[4];
$account_id=false;
if ($current=="-c")
	$current=true;
else
	$current=false;
if ($namec=="-s")
	$standart=false;
else
	$standart=true;
$gaw=new GAW();
$gaw->config['auto_presents']=false;
$gaw->cfg['owner']='bot';

if ($current==true){//выглядит как излишним, но стоит разобраться
	$res=pg_query($gaw->db,"select account_id from accounts where owner='bot' and account_id not in (select account_id from accounts_users where user_id in (select user_id from users where server_id=$server_id)) limit $new_count;");
	$resf=pg_fetch_all($res);
	$i=0;
	$m=count($resf);
	foreach($resf as $r){
		echo "=== server $server_id , count $i of $m , current acc id: ".$r['account_id'];
		$gaw->G_RegRand($server_id,$r['account_id'],$standart);
		//sendchat();
		$i++;
		//print_r($gaw->user); die();
	}
}else{
	for ($i=1;$i<=$new_count;$i++){
		echo "===$i\n";
		$gaw->G_RegRand($server_id,false,$standart);
	}
}
echo "finished\n";

?>
