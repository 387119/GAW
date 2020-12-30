<?php
// for 9 type
include "../lib/gaw.php";
/*
	140/2 грузов необходимо для сбора 1 мульта в 2 волны из расчёта до 4кк на мульте
	160/3 грузов необходимо для сбора 1 мульта в 3 волны из расчёта до 4кк на мульте
*/
// бот должен уметь работать как с суперами так и с грузами

// надо добавить возможность обновлять состояние мульта после проведения атаки, дабы вести актуальную статистику в базе.

#$res_per_mult=4000000; // усреднённое количество ресов которое может лежать на 1 мульте
$res_per_mult=4000000; // усреднённое количество ресов которое может лежать на 1 мульте, используется для подсчёта кораблей необходимых на сборщике, для их автоматической постройки
$treshold=0.75; // 75% заполнения мульта даёт сигнал о доступности к сбору
$waves='i';// i - интелектуальный выбор волн для выгребания под 0
$update_attack_mult=false;//обновление данных по мульту до и после атаки
$rate=100;
$upshift=1;
$reserved_slots=0;
// FUNCTIONS
function get_max_fly_slots(){
	global $gaw;
	// всего количество слотов
	if (!isset($gaw->user['max_fly_slots'])){
		$gaw->R_Remote('nmUser/getUserData');
		foreach ($gaw->user['remote']['nmUser/getUserData']['response']['data']['technology'] as $id=>$v){
			if ($v['tec_id']==1){
				#$gaw->user['max_fly_slots']=floor($v['lv']/2)+1;
				$gaw->user['max_fly_slots']=floor($v['lv']/2);
				break;
			}
		}
	}
	$gaw->user['max_fly_slots']--;
}
function build_ships($ships_count){
	global $gaw;
	// check if slots as free
	$s_slots=0;
	foreach ($gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'] as $si=>$s){
		if ($s['state']>0){
			$s_slots+=1;
			break;
		}
	}
	if ($s_slots>0){
		echo Date("%c")." ship slots are busy.\n";
		return false;
	}
	// check if res enought
	$res_curr=array(
		"0"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][0]['now']),
		"1"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][1]['now']),
		"2"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][2]['now'])
	);
	$res_s=array("0"=>6000,"1"=>6000,"2"=>0);
	$final_ships=$ships_count;
	//metal
	for ($i=0;$i<=2;$i++){
		$res_need[$i]=$ships_count*$res_s[$i];
		if ($res_curr[$i]<$res_need[$i]){
			$x=intval($res_curr[$i]/$res_s[$i])-1;
			if ($x>0){
				if ($x<$final_ships)
					$final_ships=$x;
			}else{
				$final_ships=0;
			}
		}
	}
	if ($final_ships<=0){
		echo Date("%c")." cannot build new ships, $ships_count requested, $final_ships calculated\n";
		return false;
	}
	// build
	$vars=array("ex_data"=>array(
		"planet_id"=>$gaw->user["mother"],
		"count"=>$final_ships,
		"unit_id"=>1
	));
	$gaw->R_Remote('nmUnit/product',$vars);
	return true;
}
function get_ships_needed(){
	global $gaw,$res_per_mult;
	$gaw->G_UpdatePlanets(false,2);
	//корабли на планке
	$planet_ships[1]=0;
	if (isset($gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'][1]['count']))
		$planet_ships[1]=$gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'][1]['count'];
	$planet_ships[22]=0;
	if (isset($gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'][22]['count']))
		$planet_ships[22]=$gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'][22]['count'];
	// корабри в полёте
	$gaw->R_Remote('nmFleet/getAllInfo');
	$fly_ships[1]=0;
	$fly_ships[22]=0;
	if (isset($gaw->user['remote']['nmFleet/getAllInfo']['response']['data']['fleet'])){
		foreach ($gaw->user['remote']['nmFleet/getAllInfo']['response']['data']['fleet'] as $fl){
			$gaw->R_Remote('nmFleet/getOneFleetInfo',array('ex_data'=>array('fleet_uid'=>$fl['fleet_uid'])));
			$fly_ships[1]+=$gaw->user['remote']['nmFleet/getOneFleetInfo']['response']['data']['bring_ship'][1];
			$fly_ships[22]+=$gaw->user['remote']['nmFleet/getOneFleetInfo']['response']['data']['bring_ship'][22];
		}
	}
	// расчёт необходимости грузов по максимуму
	//0.85
	$total_res_ships=($planet_ships[1]+$fly_ships[1])*25000+($planet_ships[22]+$fly_ships[22])*75000;
	$total_res_need=intval((floor($gaw->user['max_fly_slots']/2)*$res_per_mult)*1.1);
	$res_diff=$total_res_need-$total_res_ships;
	$create_ships=0;
	if ($res_diff>0){
		$create_ships=intval(($res_diff/25000)+1);
	}
	return $create_ships;
}
function get_free_slots(){
	global $gaw,$reserved_slots,$update_attack_mult;
	$gaw->R_Remote('nmFleet/getAllInfo');
	$ret=0;
	$min_time=99999;
	$min_attack=99999;
	$planets=array();
	if (isset($gaw->user['remote']['nmFleet/getAllInfo']['response']['data']['fleet'])){
		$usedslots=count($gaw->user['remote']['nmFleet/getAllInfo']['response']['data']['fleet']);
		$ret=$gaw->user['max_fly_slots']-$usedslots;
		if ($usedslots>0){
			foreach ($gaw->user['remote']['nmFleet/getAllInfo']['response']['data']['fleet'] as $x){
				if (($x['time']<$min_time)and($x['purpose']==1))
					$min_time=$x['time'];
				if (($x['time']<$min_attack)and($x['purpose']==0)){
					$tar=$x['target'][0]."_".$x['target'][1]."_".$x['target'][2];
					$planets[$tar]=$x['target_user_id'];
					$min_attack=$x['time'];
				}
			}
			$gaw->G_Log(G_INFO,"min back time: $min_time, min attack time: $min_attack");
		}
	}else
		$ret=$gaw->user['max_fly_slots']-$reserved_slots;
	// compare current status with attack sent status and if we do not see some planet then update info about mult
	if ($update_attack_mult==true){
		if (isset($gaw->user['attack_status'])){
			if (count($gaw->user['attack_status'])>0){
				foreach ($gaw->user['attack_status'] as $pl=>$tuid){
					if (!array_key_exists($pl,$planets)){
						$gaw->G_Log(G_INFO,"attacks to $pl finished, updating the status for mult $tuid");
						update_mult_status($tuid,$pl);
						unset($gaw->user['attack_status'][$pl]);
					}
				}
			}
		}
	}
	return $ret;
}
function calculate_wave_ships($res){
	global $gaw;
	$gaw->G_UpdatePlanets(false,2);
	$res_take=$res*0.6;
	$ships_res=array("1"=>0,"22"=>0);
	$ships=array(1=>0,22=>0);
	$cargo[1]['count']=$gaw->user["planets"][$gaw->user['mother']]['info']['data']["spacecraft"][1]['count'];
	$cargo[22]['count']=$gaw->user["planets"][$gaw->user['mother']]['info']['data']["spacecraft"][22]['count'];
	$cargo[1]['res']=$cargo[1]['count']*25000;
	$cargo[22]['res']=$cargo[22]['count']*75000;
	if ($cargo[22]['res']-75000<=$res_take){
		$ships[22]=$cargo[22]['count'];
		$res_take=$res_take-$cargo[22]['res'];
		$ships[1]=intval($res_take/25000)+1;
	}else{
		$ships[22]=intval($res_take/75000)+1;
		$res_take=0;
	}
	if ($ships[1]<0)$ships[1]=0;
	if ($ships[22]<0)$ships[22]=0;
	$rcheck=$ships[1]*25000+$ships[22]*75000;
	pg_query($gaw->db,"update bots.bot10_info set ships_1_free=".$cargo[1]['count'].",ships_22_free=".$cargo[22]['count']." where user_id=".$gaw->user['game_data']['user_id'].";");
	if (($rcheck<$res_take)or ($ships[1]>$cargo[1]['count']) or ($ships[22]>$cargo[22]['count'])){
		$gaw->G_Log(LOG_NOTICE,'cannot send wave, not enought cargos 1:'.$ships[1].'/'.$cargo[1]['count'].', 22:'.$ships[22].'/'.$cargo[22]['count'].' for res:'.intval($res*0.6));
		return false;
	}
	if ((!is_numeric($ships[1]))or(!is_numeric($ships[22])))
		return false;
	return $ships;
}
function calculate_waves_count($res){
	$outw=0;
	while (true){
		$res=$res*0.4;
		$outw++;
		if ($res<600000)
			break;
	}
	return $outw;
}
function send_attack($planet,$total_res,$target_user_id){
	global $gaw,$waves,$rate,$upshift;
	// check how many resorces on planet
	// send waves
	// check for each wave if ships are enough
	// get ships on mother
	$gaw->G_Log(G_INFO,"send attack to $planet for res $total_res , target user: $target_user_id");
	$gaw->G_UpdatePlanets(false,2);
	$planet_ships[1]=0;
	if (isset($gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'][1]['count']))
		$planet_ships[1]=$gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'][1]['count'];
	$planet_ships[22]=0;
	if (isset($gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'][22]['count']))
		$planet_ships[22]=$gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'][22]['count'];
	$gaw->G_Log(G_INFO,"ships on planet bg/sup: ".$planet_ships[1]."/".$planet_ships[22]);
	$res=$total_res;
	$attack_sent=false;
	if ($waves=='i')
		$waves=calculate_waves_count($res);
	for ($i=1;$i<=$waves;$i++){
		// вычисляем колво грузов на отправку
		$ships=calculate_wave_ships($res);
		if (($ships!=false)and(($ships[1]>0)or($ships[22]>0))){
			// отправляем и уменьшиаем ресы
			$vars=array("ex_data"=>array(
				"end_pos"=>$planet,
				"purpose"=>0,
				"upshift"=>$upshift,
				"bring_res"=>array(0=>0,1=>0,2=>0),
				"bring_ship"=>$ships,
				"rate"=>$rate,
				"start_pos"=>$gaw->user['mother']
			));
			$gaw->G_Log(G_INFO,"sending needed ships bg/sup: ".$ships[1]."/".$ships[22]);
			$gaw->R_Remote('nmFleet/sentFleet',$vars);
			// если ошибка отправки то выводим ошибку и продолжаем работать
			// ****************
			// 1601 - игрок в пузыре, ставим галочку сбора, считая что этот мульт собран
			$er=$gaw->user['remote']['nmFleet/sentFleet']['response']['data']['error'];
			if ($er==1601){
				$gaw->G_Log(G_NOTICE,"player under shield, cannot attack, set like we did");
				pg_query($gaw->db,"insert into bots.bot10_status (user_id,position,last_sent_attack) values ($target_user_id,'$planet',CURRENT_TIMESTAMP) on conflict (user_id,position) do update set last_sent_attack=CURRENT_TIMESTAMP;");
				return true;
			}
			// уменьшаем ресы к сбору
			$res=$res*0.4;
			if ($i==1){
				// устанавливаем флаг собранности после первой же волны
				$attack_sent=true;
				//запись в базу
				pg_query($gaw->db,"insert into bots.bot10_status (user_id,position,last_sent_attack) values ($target_user_id,'$planet',CURRENT_TIMESTAMP) on conflict (user_id,position) do update set last_sent_attack=CURRENT_TIMESTAMP;");
				$gaw->user['attack_status'][$planet]=$target_user_id;
				#pg_query($gaw->db,"insert into bots.bot10_status (user_id,position,last_sent_attack,ban_take_res) values ($target_user_id,'$planet',CURRENT_TIMESTAMP,true) on conflict (user_id,position) do update set last_sent_attack=CURRENT_TIMESTAMP,ban_take_res=true;");
				
			}
		}else{
			$gaw->G_Log(G_INFO,"skipping $planet");
			break;
		}
	}
	return $attack_sent;
}
function time_to_attack(){
	global $gaw;
	$return=99999;
	$gaw->R_Remote('nmFleet/getRadarFleets');
	$x=$gaw->user['remote']['nmFleet/getRadarFleets']['response']['data']['fleet'];
	if (count($x)==0)
		return $return;
	foreach ($x as $v){
		if ($v['time']<$return)
			$return=$v['time'];
	}
	return $return;
}
function hide_on_attack(){
	global $gaw;
	/*
		переходим в цикл пока есть атаки менее 4 минут
		если есть чтото на планке подымаем на минимальной скорости с запасом газа и запоминаем номер флота
		обновляем список атак
		спим 10 сек
	*/
	$time_attack=120;
	$hided=array();
	while ($time_attack<240){
		$gaw->G_UpdatePlanets(false,2);
		$ships=array(
			"1"=>$gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'][1]['count'],
			"22"=>$gaw->user['planets'][$gaw->user['mother']]['info']['data']['spacecraft'][22]['count']
		);
		$res=array(0=>0,1=>0,2=>2000000);
		if ($res[2]>$gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][2]['now'])
			$res[2]=floor($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][2]['now'])-5000;
		if ($res[2]<0)
			$res[2]=0;
		$pm=explode("_",$gaw->user['mother']);
		$planet_pirates=$pm[0]."_".$pm[1]."_17";
		$vars=array("ex_data"=>array(
			"end_pos"=>$planet_pirates,
			"purpose"=>8,
			"upshift"=>0,
			"bring_res"=>$res,
			"bring_ship"=>$ships,
			"rate"=>10,
			"start_pos"=>$gaw->user['mother']
		));
		if (($ships[1]!=0)or($ships[22]!=0)){
			$gaw->R_Remote('nmFleet/sentFleet',$vars);
			print_r($gaw->user['remote']['nmFleet/sentFleet']);
				if ($gaw->user['remote']['nmFleet/sentFleet']['response']['data']['error']==0)
					$hided[]=$gaw->user['remote']['nmFleet/sentFleet']['response']['data']['fleet_uid'];
		}
		$time_attack=time_to_attack();
		$gaw->G_Sleep(10);
	}
	/*
		опускаем все что есть и выходим из функции
	*/
	if (count($hided)>0){
		foreach ($hided as $v){
			if ($v!=0){
				$gaw->R_Remote('nmFleet/cancelFleet',array("ex_data"=>array("fleet_uid"=>$v)));
			}
		}
	}
}
function wait_activation(){
	global $gaw,$userid,$is_login;
	$relogin=false;
	while (true){
		if (!is_numeric($gaw->user['game_data']['user_id'])){
			$relogin=true;
			break;
		}
		$res=pg_query($gaw->db,"select enabled from bots.bot10_cfg where user_id=".$gaw->user['game_data']['user_id'].";");
		$resf=pg_fetch_all($res);
		$st=$resf[0]['enabled'];
		if ($st=='f'){
			$gaw->G_Log(G_INFO,"waiting for activation");
			$is_login=false;
			sleep(30);
		}else{
			if ($is_login==false)
				$relogin=true;
			break;
		}
	}
	if ($relogin==true){
		if (!is_numeric($gaw->user['game_data']['user_id']))
			$loginuserid=$userid;
		else
			$loginuserid=$gaw->user['game_data']['user_id'];
		$gaw->G_InitId($loginuserid);
		$gaw->G_Login();
		$is_login=true;
		get_max_fly_slots();
	}
}
function update_buff(){
	global $gaw;
	$gaw->R_Remote('nmBuff/getAllBuffInfo');
	$antispytime=0;
	foreach ($gaw->user['remote']['nmBuff/getAllBuffInfo']['response']['data']['info'] as $buff){
		if ($buff['buff_id']==14)
			$antispytime=$buff['time'];
	}
	pg_query("update bots.bot10_info set antispytime=$antispytime where user_id=".$gaw->user['game_data']['user_id'].";");
}
function update_mult_status($mid,$pos){
	$mult=new GAW();
	$mult->G_InitId($mid);
	$mult->G_Login();
	$mult->G_Log(G_INFO,"update info about mult $mid $pos");
	$mult->G_updatePlanets($pos,6);
	$res=pg_query($mult->db,"select ((res->>'0')::bigint+(res->>'1')::bigint+(res->>'2')::bigint) as res from planets where position='$pos' and user_id=$mid;");
	$resf=pg_fetch_all($res);
	//$mult->G_Exit();
	return $resf[0]['res'];
}
// MAIN

#$userid='21230277116';
#$userid='21531881118';//standart
#$userid='21531879118';//standart
#$userid='21531880118';//standart
#$userid='21526296116';//Supery
$userid=$argv[1];
$min_ships=180;
$gaw=new GAW();
$is_login=false;
while (true){
	//wait_activation();
	update_buff();
	$build_ships=get_ships_needed();
	if($build_ships>0){
		$gaw->G_Log(G_INFO,"build $build_ships");
		$gaw->G_Log(G_INFO,"temporary disabled");
		#build_ships($build_ships);
	}
	$fslots=get_free_slots();
	$tuid=$gaw->user['game_data']['user_id'];
	$sql="select count(*) from planets where 1=1
			and user_id in (select user_id from users where type in (7,11,12,13,17) and server_id=".$gaw->user['game_data']['server_id']." and ban=true)
			and user_id not in (select user_id from bots.bot10_status where ban_take_res=true) 
			and mother=true 
			and (string_to_array (position, '_'))[1] = (select (string_to_array (position, '_'))[1] from planets where user_id=".$gaw->user['game_data']['user_id'].") 
			and (string_to_array (position, '_'))[2]::integer >= (select (string_to_array (position, '_'))[2]::integer-(select sys_down from bots.bot10_cfg where user_id=".$gaw->user['game_data']['user_id'].") from planets where 1=1 and user_id=".$gaw->user['game_data']['user_id'].") 
			and (string_to_array (position, '_'))[2]::integer <= (select (string_to_array (position, '_'))[2]::integer+(select sys_up from bots.bot10_cfg where user_id=".$gaw->user['game_data']['user_id'].") from planets where user_id=".$gaw->user['game_data']['user_id'].");";
#	$sql="select count(*) from planets where user_id in (select user_id from users where ban=true) and user_id not in (select user_id from bots.bot10_status where ban_take_res=true) and mother=true and (string_to_array (position, '_'))[1] = (select (string_to_array (position, '_'))[1] from planets where user_id=".$gaw->user['game_data']['user_id'].") and (string_to_array (position, '_'))[2]::integer >= (select (string_to_array (position, '_'))[2]::integer-(select sys_down from bots.bot10_cfg where user_id=".$gaw->user['game_data']['user_id'].") from planets where user_id=".$gaw->user['game_data']['user_id'].") and (string_to_array (position, '_'))[2]::integer <= (select (string_to_array (position, '_'))[2]::integer+(select sys_up from bots.bot10_cfg where user_id=".$gaw->user['game_data']['user_id'].") from planets where user_id=".$gaw->user['game_data']['user_id'].");";
	$res=pg_query ($gaw->db,$sql);
	$resf=pg_fetch_all($res);
	$ready_m=$resf[0]['count'];
	$gaw->G_Log(G_INFO,"total slots ".$gaw->user['max_fly_slots'].", free slots $fslots , ready mults: $ready_m");
	pg_query($gaw->db,"update bots.bot10_info set slots_total=".$gaw->user['max_fly_slots'].",slots_free=$fslots where user_id=".$gaw->user['game_data']['user_id'].";");
	if ($fslots>=$waves){//проверяем есть ли свободние слоты для атаки 4-5
		// get planet where sent attack
	#	$sql="select user_id,total_res,position from (select user_id,(res->>'0')::bigint+(res->>'1')::bigint+(res->>'2')::bigint as total_res,(res->>'2')::bigint as gaz_res,position from planets where user_id in (select user_id from users where ban=false) and mother=true and (string_to_array (position, '_'))[1] = (select (string_to_array (position, '_'))[1] from planets where user_id=".$gaw->user['game_data']['user_id'].") and (string_to_array (position, '_'))[2]::integer >= (select (string_to_array (position, '_'))[2]::integer-(select sys_down from bots.bot10_cfg where user_id=".$gaw->user['game_data']['user_id'].") from planets where user_id=".$gaw->user['game_data']['user_id'].") and (string_to_array (position, '_'))[2]::integer <= (select (string_to_array (position, '_'))[2]::integer+(select sys_up from bots.bot10_cfg where user_id=".$gaw->user['game_data']['user_id'].") from planets where user_id=".$gaw->user['game_data']['user_id'].") order by total_res desc, gaz_res desc) as t where user_id in (select user_id from bots.bot9_status where finished=true) and user_id in (select user_id from users where type in (7,11,12,13)) and t.total_res is not null order by total_res desc limit 1;";//// change user_id
		while (true){
		$sql="select user_id,total_res,position from
        	(select user_id,(res->>'0')::bigint+(res->>'1')::bigint+(res->>'2')::bigint as total_res,
	                (res->>'2')::bigint as gaz_res,position
        	from planets
	        where user_id in (select user_id from users where ban=false)
			and user_id in (select user_id from vusers where type in (7,11,12,13,17))
                	and user_id not in (select user_id from bots.bot10_status where last_sent_attack>now()-interval '2 hours')
	                and mother=true
        	        and (string_to_array (position, '_'))[1] =
                	        (select (string_to_array (position, '_'))[1] from planets where user_id=$tuid)
	                and (string_to_array (position, '_'))[2]::integer >=
        	                (select (string_to_array (position, '_'))[2]::integer-(select sys_down from bots.bot10_cfg where user_id=$tuid)
                	        from planets
	                        where user_id=$tuid)
        	        and (string_to_array (position, '_'))[2]::integer <= (select (string_to_array (position, '_'))[2]::integer+(select sys_up from bots.bot10_cfg where user_id=$tuid) from planets where user_id=$tuid)
	        ) as t
        	where 1=1
                	and t.total_res is not null order by total_res desc limit 1;";
			$res=pg_query($gaw->db,$sql);
			$resf=pg_fetch_all($res);
			if (is_numeric($resf[0]['user_id'])){
				//update info about first user
				//определение того что мульт почищен, и обновление состояния ресов на нём, создаём массив с планетами на которые отправленна атака, удаляем из массива если на мульт атаки больше нет, по факту удаления обновляем данные по мульту
				$tres=$resf[0]['total_res'];
				if ($update_attack_mult==true){
					$gaw->G_Log(G_INFO,"update status about ".$resf[0]['user_id']." before attack");
					$tres=update_mult_status($resf[0]['user_id'],$resf[0]['position']);
				}
				if ($tres>=$resf[0]['total_res'])
					break;
				$gaw->G_Log(G_INFO,"less resources than expected, mult: ".$resf[0]['user_id'].", res expected: ".$resf[0]['total_res'].", res got: $tres");
			}else{
				$gaw->G_Log(G_INFO,"user_id is not numeric, taking next user");
				$gaw->G_Sleep (10);
			}
		}
		if (isset($resf[0]['position']))
			send_attack($resf[0]['position'],$tres,$resf[0]['user_id']);
	}
	// прошло до 20-25 секунд
	// сборщикам достроить радар 4 ур (требуется солнышко 5), радар видит 4 минуты, этого достаточно так как программа расчитана на 2 минуты и в оффлайн + запас 2 минуты на случай нескольких волн
	if (time_to_attack()<120){//проверяем идёт ли атака на мульта и если меньше чем 2 минуты до прилёта то.
		$gaw->G_Log(G_NOTICE,"attack detected, hiding all ships");
		hide_on_attack();//отправляем грузы на пиратов с газом без продления сейва на минимуме и ожидаем пока закончатся атаки, после этого возвращаем грузы
	}
	$gaw->R_Remote('nmFleet/getPlanetQuardInfo',array("ex_data"=>array("planet_id"=>$gaw->user['mother'])));
	$protect=count($gaw->user['remote']['nmFleet/getPlanetQuardInfo']['response']['data']['info']);
	if ($protect>0)$is_protected='true';
	else $is_protected='false';
	$sql="insert into bots.bot10_info (user_id,is_protected,last_update) values (".$gaw->user['game_data']['user_id'].",$is_protected,now()) on conflict (user_id) do update set is_protected=$is_protected,last_update=now();";
	pg_query($gaw->db,$sql);
	$gaw->G_Sleep(30);
}
?>

