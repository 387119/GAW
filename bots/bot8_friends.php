<?php
include "../lib/gaw.php";
//$user=21540082118;//diloya

// type=8
//$user=21564455118;//wowoyu
//$user=21564459118;//nizuze
//$user=21564458118;//cigove
$user=21564457118;//kiyana
//$user=21564454118;//somipa

$gaw=new GAW();
$gaw->G_InitId($user);
$gaw->EXIT_ON_ERROR=true;
$gaw->G_Login();
$server_id=$gaw->user['game_data']['server_id'];
while (true){
	$res=pg_query($gaw->db,"select extract('epoch' from now()-marker_update) as last from bots.bot8_servers where server_id=$server_id;");
	$resf=pg_fetch_all($res);
	if ($resf[0]['last']<=60){
		$gaw->R_Remote('nmFriendEx/getFrientList');
		$sql="";
		foreach ($gaw->user['remote']['nmFriendEx/getFrientList']['response']['data']['friends'] as $f){
			$uid=$f['user_id'];
			$uname=$f['user_name'];
			$us=-1;	
			switch ($f['heart_type']){
				case 's':$us=$f['heart_value'];break;
				case 'm':$us=$f['heart_value']*60;break;
				case 'h':$us=$f['heart_value']*3600;break;
				case 'd':$us=$f['heart_value']*86400;break;
			}
			$sql=$sql."insert into bots.bot8_friends (server_id,user_id,user_name,last_online,last_update) values (".$gaw->user['game_data']['server_id'].",$uid,E'$uname',$us,now()) on conflict (user_id) do update set last_update=now(),last_online=$us;\n";
		}
		pg_query($gaw->db,$sql);
		$gaw->G_Sleep(60);
	}
}
?>

