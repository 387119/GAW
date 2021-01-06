<?php
include "../lib/gaw.php";
$user=$argv[1];
/*
 * TODO
 * - проревьювить скрипт
 * - переделать под новую базу
 * - конфиги типов прокачки брать из ресурсных файлов
 * - переделать скрипт так чтоб он ранился в цикле прыгая по командирам и запукая что не запутилось
 * - данные об командирах требующие активности брать из статусов
 * - переделать подарки так чтоб таймаут ожидания 5 или x минут для первого подарка был в том случае если время на этот подарок сбрасывается при входе, но скорей всего это будет вынесено на уровень библиотеки (взятие подарков в принципе)
 */
$timewait=1200;// 20 мин ожидать, специально для того чтоб первый подарок успевал браться 
// FUNCTION
function get_bst(){
	global $gaw;
	$ret=array();
	$res=pg_query($gaw->db,"select step_typeid||'_'||step_id||'_'||step_value||case when blocker then '_1' else '_0' end as bst from bots.bot9_steps where user_typeid=(select type from users where user_id=".$gaw->user['game_data']['user_id'].") and enabled=true order by priority;");
	$resf=pg_fetch_all($res);
	if (!is_array($resf))
		die("no steps found.");
	foreach ($resf as $f){
		$ret[]=$f['bst'];
	}
	return $ret;
}
function remove_finished_build(){
	global $gaw,$bst;
	$gaw->G_updatePlanets(false,6);
	$b_done=0;
	for ($bc=0;$bc<=17;$bc++){
	//foreach (array(0,1,2,3,7,8,9) as $bc){
		$btek=$gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'][$bc]['lv'];
		foreach ($bst as $k=>$kb){
			list($bt,$bu,$bl)=split('_',$kb);
			if ($bt=='0'){
				if(($bu==$bc)and($bl<=$btek)){
					$b_done++;
					unset($bst[$k]);
				}
			}
		}
	}
	echo "BUILDS, done:$b_done\n";
}
function remove_finished_laba(){
	global $gaw,$bst;
	$gaw->G_Ping();
	foreach ($bst as $k=>$kb){
		list($bt,$bu,$bl,$xxx)=split('_',$kb);
		if ($bt==1){
			print_r($gaw->user['remote']['nmUser/getGameDataEx']['response']['data']);
			if ($gaw->user['remote']['nmUser/getGameDataEx']['response']['data']['tec_info'][$bu]['lv']>=$bl)
				unset($bst[$k]);
		}
	}
	// for and remove what done
	// 
}
function remove_finished_ships(){
	// TBD
}
function remove_finished(){
	global $bst,$gaw;
	remove_finished_build();
	remove_finished_laba();
	$x=array_values($bst);
	if (is_array($x)) if (count($x)>0){
		echo "current status position is: ".$x[0]."\n";
		list ($x1,$x2,$x3,$x4)=explode('_',$x[0]);
		echo "$x1 $x2 $x3";
		pg_query($gaw->db,"update bots.bot9_status as bs1 set current_status=bs2.priority from bots.bot9_steps as bs2 where bs1.user_id=".$gaw->user['game_data']['user_id']." and bs2.user_typeid=(select type from users where user_id=".$gaw->user['game_data']['user_id'].") and bs2.step_typeid=$x1 and bs2.step_id=$x2 and step_value=$x3;");
	}
}
function get_slots(){
	// Определяем есть ли постройки в процессе
	global $gaw,$timewait;
	$gaw->G_updatePlanets(false,2);
	$ret=array(
		"0"=>array('id'=>-1,"time"=>0),
		"1"=>array("id"=>-1,"time"=>0),
		0);
	$b_slots=0;
	$b_time=0;
	foreach ($gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'] as $bi=>$b){
		if ($b['state']>0){
			$b_slots+=1;
			if ($b['time']>$ret[0]['time']){
				$ret[0]["id"]=$bi;
				$ret[0]["time"]=$b['time'];
			}
		}
	}
	$gaw->R_Remote('nmUser/getUserData');
	foreach ($gaw->user['remote']['nmUser/getUserData']['response']['data']['technology'] as $id=>$v){
		if ($v['state']>0){
			if ($v['finish_time']>$ret[1]['time']){
				$ret[1]['time']=$v['finish_time'];
				$ret[1]['id']=$v['tec_id'];
			}	
		}
	}
//	if ($b_slots>0){
//		echo "Build still in progress\n";
//		die();
//	}
	return $ret;
}
function wait($type){
	//0,1,2 - здания/лаба/корабли
	global $gaw,$timewait;
	$slots=get_slots();
	if (($type==0)and($slots[$type]['time']>0)){
		$slots[$type]['time']=build_speedup($slots[$type]['id'],$slots[$type]['time']);
		//$slots=get_slots();
	}
	if ($slots[$type]['time']>0){
	pg_query($gaw->db,"insert into bots.bot9_status (user_id,finished,uplevel_finish) values (".$gaw->user['game_data']['user_id'].",false,now()+interval '".$slots[$type]['time']." seconds') on conflict (user_id) do update set uplevel_finish=now()+interval '".$slots[$type]['time']." seconds';");
		if ($slots[$type]['time']>$timewait){
			echo "max time for finish is ".$slots[$type]['time']." exiting..\n";
			die();
		}else{
			if (isset($slots[$type]['id'])){
				echo "waiting ".$slots[$type]['time']." for finish...\n";
				$vars=array("ex_data"=>array("planet_id"=>$gaw->user['mother'],"build_id"=>$slots[$type]['id']));//prepare for type=0 before sleep
				$gaw->G_Sleep($slots[$type]['time']);
				if ($type==0)
					$gaw->R_Remote('nmBuild/finishUpgrade',$vars);
			}else{
				echo "id in slots not found, user ".$gaw->user['game_data']['user_id']."\n";
				print_r($slots);
			}
		}
	}
	remove_finished();
}
function uplevel($type,$id){
	global $gaw,$timewait;
	wait($type);
	$start=-1;//не строить ничего
	$gaw->G_updatePlanets(false,2);
	$res_curr=array(
		"0"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][0]['now']),
		"1"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][1]['now']),
		"2"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][2]['now'])
	);
	// определяем текущий уровень
	switch ($type){
		case "0":
			$rlv=$gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'][$id]['lv'];
			break;
		case "1":
			$gaw->R_Remote('nmUser/getUserData');
			$rlv=$gaw->user['remote']['nmUser/getUserData']['response']['data']['technology'][$id]['lv'];
			break;
	}
	$res_need=get_res_need($type,$id,$rlv);
	if (($res_curr[0]>=$res_need['res0'])and($res_curr[1]>=$res_need['res1'])and($res_curr[2]>=$res_need['res2']))
		$start=1;
	if ($start==1){
		echo "start up level for type $type and id $id\n";
		switch ($type){
			case "0":
				$vars=array("ex_data"=>array("planet_id"=>$gaw->user['mother'],"build_id"=>$id));//only buildings
				$gaw->R_Remote('nmBuild/upgrade',$vars);
				$time=$gaw->user['remote']['nmBuild/upgrade']['response']['data']['time'];
				$time=build_speedup($id,$time);
				break;
			case "1":
				$vars=array("ex_data"=>array("planet_id"=>$gaw->user['mother'],"tec_id"=>$id));
				$gaw->R_Remote('nmTec/upgrade',$vars);
				$time=$gaw->user['remote']['nmTec/upgrade']['response']['data']['time'];
				break;
		}
		if (!is_numeric($time))
			$time=0;
		echo "time for upgrade type $type with $id from level $rlv is $time seconds\n";
		pg_query($gaw->db,"insert into bots.bot9_status (user_id,finished,uplevel_finish) values (".$gaw->user['game_data']['user_id'].",false,now()+interval '$time seconds') on conflict (user_id) do update set uplevel_finish=now()+interval '$time seconds';");
		if ($time<=$timewait){
			echo "waiting... $time seconds\n";
			$gaw->G_Sleep($time+10);
			switch ($type){
				case "0":
					$gaw->R_Remote('nmBuild/finishUpgrade',$vars);
					break;
			}
		}else{
			echo "much time to wait, exiting\n";
			die();
		}
		return true;
	}
	return false;
}
function get_res_need($type,$id,$level){
	global $gaw;
	$ret=array("res0"=>0,"res1"=>0,"res2"=>0);
	if (($type==1)and($id==1)){
		$ret=calc_laba_fleetslots($level);
	}else{
		$sql="select r0*(d0^$level) as res0,r1*(d1^$level) as res1,r2*(d2^$level) as res2 from uplevel where typeid=$type and id=$id;";
		$res=pg_query($gaw->db,$sql);
		$resf=pg_fetch_all($res);
		$ret=$resf[0];
	}
	return $ret;
}
function calc_laba_fleetslots($level){
	$ret=array("res0"=>0,"res1"=>0,"res2"=>0);
	if ($level>3){
		$x1=calc_laba_fleetslots($level-1);
		$x3=calc_laba_fleetslots($level-4);
		$ret['res1']=$x1['res1']+$x3['res1'];
		$ret['res2']=$x1['res2']+$x3['res2'];
	}else{
		$ret['res1']=800*(2**$level);
		$ret['res2']=1200*(2**$level);
	}
	return $ret;
}
function get_user_speed_items(){
	global $gaw;
	$ret=array();
	$gaw->R_Remote('nmItem/getItemCountInfo');
	foreach ($gaw->user['remote']['nmItem/getItemCountInfo']['response']['data']['info'] as $v){
		if (($v['id']>=1)and($v['id']<=8)){
			$ret[$v['id']]=$v['count'];
		}
	}
	return $ret;
}
function build_speedup($id,$time){
	global $gaw;
	$speedupids=array("1"=>300, "2"=>900, "3"=>1800, "4"=>3600, "5"=>10800, "6"=>28800, "7"=>54000, "8"=>86400);
	$user_items=get_user_speed_items();
	for ($i=8;$i>=1;$i--){
		if ($user_items[$i]>0){
			for ($j=1;$j<=$user_items[$i];$j++){
				if ($speedupids[$i]<$time){
					$vars=array();
					$vars['ex_data']=array("planet_id"=>$gaw->user['mother'],"count"=>1,"role_object"=>1,"id"=>$i,"ex_id"=>$id);
					$gaw->R_Remote("nmItem/useItem",$vars);
					$time-=$speedupids[$i];
				}else
					break;
			}
		}
	}
	return $time;
}
// START
$gaw=new GAW();
$gaw->G_InitId($user);//инициализировать командира
$bst=get_bst();//получить список уровней зданий для этого типа командира
$gaw->G_Login();//войти в командира
$gaw->R_gatherPresents();//собрать подарки
remove_finished();//убрать те обьекты из списка которые уже выполнены
if (count($bst)==0){
	pg_query($gaw->db,"insert into bots.bot9_status values (".$gaw->user['game_data']['user_id'].",true) on conflict (user_id) do update set finished=true;");
}
/*
	смотрим есть ли постройки требующие финализации, если да то финализируем
		// nmPlanet/getPlanetInfo.response.data.build.ud.state=1 - идёт постройка
		// найти бы флаг, но пока не нашёл.
	Определяем есть ли свбодные слоты для постройки на материнской (пока) планете
	Если да то определяем достаточно ли ресов на постройку робо и нужно ли её строить
		запускаем постройку робо, переходим на конечную проверку.
	Определяем следующую по порядку необходимую постройку и проверяем достаточно ли ресов
		запускаем постройку и переходим на конечную проверку
		иначе выходим
	конечная проверка, если время постройки не превышает 15 минут то ждём (15 минут специально чтоб взять первый подарок для новых акков), финализируем постройку и переходим на начало на новую постройку
		иначе выходим
*/
//Строим постройки
$gaw->G_updatePlanets(false,2);
while (true){
	remove_finished();
	print_r($bst);
	$bd=array_shift(array_values($bst));
	list($btype,$bnum,$blv,$ismandatory)=split('_',$bd);
	echo "trying up type $btype id $bnum to $blv\n";
	if (!uplevel($btype,$bnum)){
		if ($ismandatory==1){
			pg_query($gaw->db,"insert into bots.bot9_status (user_id,finished,uplevel_finish) values (".$gaw->user['game_data']['user_id'].",false,now()+interval '10 hours') on conflict (user_id) do update set uplevel_finish=now()+interval '10 hours';");
			echo "can not up level for type $btype with id $bnum to $blv, maybe due to not enough res, exiting\n";
			break;
		}
	}
}
$gaw->G_Exit();
?>
