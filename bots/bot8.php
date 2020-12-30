<?php
include "../lib/gaw.php";
/*
	* - продумать и сделать автосоздание нового аккаунта и новых командиров для новых серверов или пересоздание для старых, пока считаем что они уже существуют
	1 - входим в онлайн командира
	2 - получаем список альянсов и пишем его в базу (server_id,alliance_name,leader_name,score,last_update,marker_update)
		// если marker_update < 20 секунд то обновляем, если больше то игнорируем
	3 - входим в бесконечный цикл 
	4 - получаем не устаревшие маркеры
	5 - получаем и обновляем список альянсов по маркерам (server_id,alliance_name,user_name,vip_lv,commander_lv,score,heart_type,heart_value,last_update)
	7 - периодически (раз в 5 часов, обновляем список альянсов)
	
*/
function update_alliance_list(){
	global $gaw;
	$gaw->R_Remote('nmAlliance/getAlliancesInfo',array("ex_data"=>array("start"=>0,"count"=>100)));
	$sql="";
	foreach ($gaw->user['remote']['nmAlliance/getAlliancesInfo']['response']['data']['info'] as $a){
		$sql.="insert into bots.bot8_alliances (server_id,alliance_name,leader_id,leader_name,score,last_update,marker_update) 
			values (".$gaw->user['game_data']['server_id'].",'".str_replace("'","''",$a['alliance_name'])."',".$a['leader_id'].",'".str_replace("'","''",$a['leader_name'])."',".$a['score'].",CURRENT_TIMESTAMP,null)
			on conflict (server_id,alliance_name)
			do update set leader_id=".$a['leader_id'].",leader_name='".str_replace("'","''",$a['leader_name'])."',score=".$a['score'].",last_update=CURRENT_TIMESTAMP;\n";
	}
	pg_query($gaw->db,$sql);
}
function update_alliance_members($alliance_name){
	global $gaw;
	echo Date("c")." DETAIL: updating alliance $alliance_name\n";
	$gaw->R_Remote('nmAlliance/getMemberList',array("ex_data"=>array("alliance_name"=>$alliance_name)));
	$sql="";
	foreach ($gaw->user['remote']['nmAlliance/getMemberList']['response']['data']['members'] as $m){
		switch ($m['heart_type']){
			case 's':$sd=1;break;
			case 'm':$sd=60;break;
			case 'h':$sd=3600;break;
			default:$sd=86400;break;
		}
		$last_online=$m['heart_value']*$sd;
		$sql.="insert into bots.bot8_members (server_id,alliance_name,user_id,user_name,vip_lv,commander_lv,score,last_online,last_update) 
			values (".$gaw->user['game_data']['server_id'].",'$alliance_name',".$m['user_id'].",'".str_replace("'","''",$m['user_name'])."',".$m['vip_lv'].",".$m['commander_lv'].",".$m['score'].",$last_online,CURRENT_TIMESTAMP)
			on conflict (server_id,alliance_name,user_id)
			do update set user_name='".str_replace("'","''",$m['user_name'])."',vip_lv=".$m['vip_lv'].",commander_lv=".$m['commander_lv'].",score=".$m['score'].",last_online=$last_online,last_update=CURRENT_TIMESTAMP;
			update bots.bot8_alliances set member_update=now() where server_id=".$gaw->user['game_data']['server_id']." and alliance_name='$alliance_name';\n";
	}
	$sql.="delete from bots.bot8_members where server_id=".$gaw->user['game_data']['server_id']." and alliance_name='$alliance_name' and last_update < CURRENT_TIMESTAMP - interval '20 sec';";
	pg_query($gaw->db,$sql);
	echo Date("c")." DETAIL: finish updating alliance $alliance_name\n";
}

#$user="21518698101";//btt10453671 - Oracle
$user=$argv[1];
$gaw=new GAW();
$gaw->EXIT_ON_ERROR=true;
$gaw->G_InitId($user);
$gaw->G_Login();
update_alliance_list();
while (true){
	$res=pg_query("select alliance_name from bots.bot8_alliances where server_id=".$gaw->user['game_data']['server_id']." and marker_update > CURRENT_TIMESTAMP - interval '20 sec';");
	$resf=pg_fetch_all($res);
	if ((count($resf)>0)and(is_array($resf))){
		foreach($resf as $a){
			update_alliance_members(str_replace("'","''",$a['alliance_name']));
		}
	}
	$gaw->G_Sleep(5);
}
?>
