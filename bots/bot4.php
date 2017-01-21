<?php
include "../lib/gaw.php";
$user=$argv[1];
#$user="Kоlt";
$gaw=new GAW($user);
$gaw->G_login();
#$gaw->R_getAllInfo();
#$gaw->R_cancelFleet(14828454);
#$gaw->R_getUserPlanetList();
#$gaw->R_getUniverse(171,380);
#$gaw->R_sentFleet(0,array("from"=>$gaw->user['mother'],"to"=>"172_380_2"),array(0=>0,1=>0,2=>0),array("2"=>1));
#$gaw->R_sentFleet(6,array("from"=>$gaw->user['mother'],"to"=>"110_517_9"),array(0=>0,1=>0,2=>0),array("2"=>1));
#$gaw->R_pushResBank($gaw->user['mother'],2,100000.11234);

#$gaw->R_pushResBank
#$gaw->R_overLooker($gaw->user['mother']);
#$gaw->R_overLooker("1_1_1");
#$gaw->R_getMailList();
#$gaw->R_getMailInfo(28097914,null);
#print_r($gaw->user);

/*
	1 - Входим на аккаунт
	2 - Опускаем сейв "возврат сейва"
	3 - Ожидаем атаки "ожидание атаки, коры ..."
	4 - При обнаружении атаки проверяем авторизацию аккаунта
		4.1 - Если аккаунт авторизирован то шлём приглашение на размещение зонда
			4.1.1 - Подымаем грузы в сейв без ресов "Приглос на зонд кинут, грузы в сейве."
			4.1.2 - Ожидаение снятия ресов.
		4.2 - Если не авторизирован то подымаем грузы с ресами в сейв
	5 - установка статуса, установка ресов, снимаем галку
	6 - оффлайн
*/

pg_query($gaw->db,"update bot4 set status=2,last_update=now(),comment='Возврат сейва для сьёма ресов' where user_name='$user';");
#exec("../wiki/set_comment.sh '".$gaw->user["user_name"]."' 'Возврат сейва для сьёма ресов'");
echo  Date("c")."Возврат сейва\n";
$gaw->R_getAllInfo();//check all fleets
if ($gaw->user["remote_last_results"]["R_getAllInfo"]["data"]["error"]==0){
	foreach ($gaw->user["remote_last_results"]["R_getAllInfo"]["data"]["fleet"] as $val){
		$pos=$val["from"][0]."_".$val["from"][1]."_".$val["from"][2];
		if ($gaw->user['mother']==$pos){
			if ($val["purpose"]!=1){
				echo "put back fleet id ".$val["fleet_uid"]."\n";
				$gaw->R_cancelFleet($val['fleet_uid']);
			}
		}
	}
        //$gaw->R_getAllInfo();//check all fleets again
        while (true){
                $gaw->R_getAllInfo();//check all fleets again
                $waittime=0;
                if ($gaw->user["remote_last_results"]["R_getAllInfo"]["data"]["error"]==0){
                        foreach ($gaw->user["remote_last_results"]["R_getAllInfo"]["data"]["fleet"] as $val){
                                $pos=$val["from"][0]."_".$val["from"][1]."_".$val["from"][2];
                                if ($gaw->user['mother']==$pos){
                                        if ($val["purpose"]==1)
                                                if ($val["time"]>$waittime)
                                                        $waittime=$val["time"];
                                }
                        }
                        echo "fleets will come in $waittime seconds, waiting...\n";
                        $gaw->G_sleep ($waittime);
                }
                if ($waittime==0)
                        break;
        }
}
pg_query($gaw->db,"update bot4 set last_update=now(),comment='Ожидание атаки на ".$gaw->user['mother']."' where user_name='$user';");
#exec("../wiki/set_comment.sh '".$gaw->user["user_name"]."' 'Ожидание атаки на ".$gaw->user['mother']."'");
echo  Date("c")." Ожидание атаки в течении 2 часов\n";
$start_wait_attack=time();
$attack_planet="";
while ($start_wait_attack+7200>time()){
	$gaw->R_getRadarFleets();
	if (count($gaw->user["remote_last_results"]["R_getRadarFleets"]["data"]["fleet"])>0){
		//attack set planet and break
		$min_attack_time=10000;
		foreach($gaw->user["remote_last_results"]["R_getRadarFleets"]["data"]["fleet"] as $fleet){
			if ($fleet["time"]<$min_attack_time){
				$min_attack_time=$fleet["time"];
				$attack_gal=$fleet["from"][0];
				$attack_sys=$fleet["from"][1];
				$attack_planet=$fleet["from"][2];
				$attack_user_id=$fleet["start_user_id"];
			}
		}
	}
	if ($attack_planet!="")
		break;

	$gaw->G_sleep(15);
}
$access="reject";
echo  Date("c")." Атака с ".$attack_gal."-".$attack_sys."-".$attack_planet."\n";
if ($attack_planet!=""){
	$gaw->R_getUniverse($attack_gal,$attack_sys);
	$attack_user=$gaw->user['remote_last_results']['R_getUniverse']['data']['planets'][$attack_planet]['user_name'];
	echo "$attack_gal $attack_sys $attack_planet $attack_user\n";
	$res=pg_query($gaw->db,"select case when (select owner from users where user_name='$user')=(select owner from users where user_name='$attack_user') then 'permit' else 'reject' end as access;");
	$resf=pg_fetch_array($res,NULL,PGSQL_ASSOC);
	$access=$resf['access'];
	#$access=exec ("../wiki/get_attack_permit.sh '".$gaw->user["user_name"]."' '$attack_user'");
}
echo "$attack_gal $attack_sys $attack_planet $attack_user_id $attack_user $access\n";
//Снимаем галочку
#exec ("../wiki/set_bot4.sh '".$gaw->user["user_name"]."'");
if ($access=="permit"){
	//кидаем приглос зондом
	$gaw->R_applyQuard($gaw->user['mother'],$attack_user_id);
	//сбор разрешён, подымаем пустые грузы
	$gaw->G_Save(false);
	//устанавливаем колво ресов отправленных в сейв
	pg_query($gaw->db,"update bot1 set res0=".intval($gaw->user['last_fleet_save']['res'][0]).",res1=".intval($gaw->user['last_fleet_save']['res'][1]).",res2=".intval($gaw->user['last_fleet_save']['res'][2])." where user_name='$user';");
	#exec("../wiki/set_res.sh '".$gaw->user["user_name"]."' ".$gaw->user['last_fleet_save']['total_res']." ".$gaw->user['last_fleet_save']['total_percent']);
	//смотрим сколько осталось флота на планке  после сейва и пишем сумму на вики
	$gaw->R_getSpacecraft($gaw->user['mother']);
	$fleet_rest=0;
	foreach($gaw->user['remote_last_results']['R_getSpacecraft']['data']['data'] as $v){
		$fleet_rest+=$v;
	}
	pg_query($gaw->db,"update bot4 set last_update=now(),comment='Грузы в сейве, флот остаток $fleet_rest, зонд запрошен, летит $attack_user' where user_name='$user';");
	#exec("../wiki/set_comment.sh '".$gaw->user["user_name"]."' 'Грузы в сейве, флот остаток $fleet_rest, зонд запрошен, летит $attack_user'");
	//Ожидаем прилёта первой атаки выходим и устанавливаем статус времени снятия ресов
	$gaw->G_sleep($min_attack_time);
	pg_query($gaw->db,"update bot4 set last_update=now(),status=0,comment='Завершено успешно' where user_name='$user';");
	#exec("../wiki/set_comment.sh '".$gaw->user["user_name"]."' '".time()."'");
}
else{
	//сбор запрешён или таймаут ожидания атаки, сейвим все.
	$gaw->G_Save();
	//устанавливаем колво ресов отправленных в сейв
	pg_query($gaw->db,"update bot1 set res0=".intval($gaw->user['last_fleet_save']['res'][0]).",res1=".intval($gaw->user['last_fleet_save']['res'][1]).",res2=".intval($gaw->user['last_fleet_save']['res'][2])." where user_name='$user';");
	#exec("../wiki/set_res.sh '".$gaw->user["user_name"]."' ".$gaw->user['last_fleet_save']['total_res']." ".$gaw->user['last_fleet_save']['total_percent']);
	pg_query($gaw->db,"update bot4 set last_update=now(),status=0,comment='Завершено неуспешно, летел неавторизированный $attack_user' where user_name='$user';");
	//Ожидаем прилёта первой атаки выходим и устанавливаем статус время отправки в сейв
	#exec("../wiki/set_comment.sh '".$gaw->user["user_name"]."' '".time()."'");
}
#print_r($gaw->user);
$gaw->G_Exit();
?>

