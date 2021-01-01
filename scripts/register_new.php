<?php
include "../lib/gaw.php";
function sendchat(){
	global $gaw;
	$tick=date("Y-m-d H:i:s");
	$count=20;
	$text="hi";
	$vars['ex_data']=array("text"=>$text,"count"=>$count,"tick"=>$tick);
	$gaw->R_Remote('nmChat/sendChat',$vars);
}
if (count($argv)<3){
	echo "usage php register_new.php <count> <server_id> [-c/n -s]
		-c - use current free logins and passwords
		-s = use not standart name convention G00000000S00";
	die();
}
$new_count=$argv[1];
$new_server=$argv[2];
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

if ($current==true){
	$res=pg_query($gaw->db,"select account_id from accounts where owner='bot' and account_id not in (select account_id from accounts_users where user_id in (select user_id from users where server_id=$new_server)) limit $new_count;");
	$resf=pg_fetch_all($res);
	$i=0;
	$m=count($resf);
	foreach($resf as $r){
		echo "=== server $new_server , count $i of $m , current acc id: ".$r['account_id'];
		$gaw->G_RegRand($new_server,$r['account_id'],$standart);
		//sendchat();
		$i++;
		//print_r($gaw->user); die();
	}
}else{
	for ($i=1;$i<=$new_count;$i++){
		echo "===$i\n";
		$gaw->G_RegRand($new_server,false,$standart);
		//sendchat();
	}
}
echo "finished\n";

?>
