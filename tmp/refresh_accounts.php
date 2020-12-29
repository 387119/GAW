<?php
include "../lib/gaw.php";
$gaw=new GAW();
// получить список серверов
$gaw->G_serverList();
$srv=array();
foreach ($gaw->user['remote']['/spx_gsm/api_game_server/serverList/ING004/google/0201/0/ru']['response']['data']['server'] as $s){
	if ($s['state']!=5)
		$srv[]=$s['server_id'];
}
// получить список аккаунтов
$res=pg_query($gaw->db,"select acccount from accounts where refresh=true or password_hash is null;");
$resf=pg_fetch_all($res);
foreach($resf as $a){
	echo "working with account ".$a['acccount']."\n";
	foreach($srv as $s){
		echo "working with server $s\n";
		$gaw->G_InitAcc($a['acccount'],$s);
		$gaw->G_Login();//логин и обновление таблицы списка пользователей
	}
	pg_query($gaw->db,"update accounts set refresh=false where account_id=".$gaw->user['game_data']['account_id'].";");
}
 
?>
