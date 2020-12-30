<?php
/*
	* - будем использовать 2 переменных, 1 - для капитана и 2 для мультов
	1 - логинимся в капитана 
	2 - получаем данные по активности станции и активности на техах
	3 - получаем список мультов в альянсе, мультов получаем выборкой из членов альянса с фильтрацией в базе по типу мульта
		3.1  заходим в каждого и скидываем ресы на станцию по максимуму
	2 если станция не качается смотрим доступность ресов и запускаем на прокачку если возможно (не максимум и хватает ресов)
	2 если техи не качаются выбираем самый слабый из техов и пускаем на прокачку если возможно (не максимум и хватает ресов)
	4 - проверяем 
		4.2 получаем данные по станции, техам, ускорителям, тм, ресам
		4.3 если станция или техи качаются то ниже, тоесли не качается то выходим из 4 цикла
			4.3.1 сливаем газ
			4.3.2 ускорители 
			4.3.3 тм
		4.4 если нет ни ускорителей и тм <=10 то выводим мульт из ала и ставим флаг "больше не использовать" - status=2
	4 - получаем количество свободных мест в альянсе
		3.1 если места есть то выбираем список мультов которые ещё небыли использованны и заводим в альянс их
	5 - если дошли до этого пункта, значит вы слили, убрали и добавили новых все что можно сегодня, поэтому можно выходить на 1 сутки
*/
include "../lib/gaw.php";

/* BEGIN GLOBAL VARS */
$alliance_name='';//see after
$commander_id=21522467116;
$speedupids=array("1"=>300, "2"=>900, "3"=>1800, "4"=>3600, "5"=>10800, "6"=>28800, "7"=>54000, "8"=>86400);
/* END GLOBAL VARS */

/*  BEGIN FUNCTIONS   */
function get_station_details(){
	global $gawk;
	$st=array();
	$gawk->R_Remote('nmControl/openControl');// показует сколько есть ресов на станции, текущий уровень и сколько надо для постройки следующего, сколько можно закидывать ресов
	$st['level']=$gawk->user['remote']['nmControl/openControl']['response']['data']['control']['level'];
	$st['res']['cur'][0]=$gawk->user['remote']['nmControl/openControl']['response']['data']['control']['warehouse'][0];
	$st['res']['cur'][1]=$gawk->user['remote']['nmControl/openControl']['response']['data']['control']['warehouse'][1];
	$st['res']['cur'][3]=$gawk->user['remote']['nmControl/openControl']['response']['data']['control']['warehouse'][3];
	$st['res']['up'][0]=$gawk->user['remote']['nmControl/openControl']['response']['data']['config'][$st['level']]['cost']['res'][0];
	$st['res']['up'][1]=$gawk->user['remote']['nmControl/openControl']['response']['data']['config'][$st['level']]['cost']['res'][1];
	$st['res']['up'][3]=$gawk->user['remote']['nmControl/openControl']['response']['data']['config'][$st['level']]['cost']['res'][3];
	$st['res']['add'][0]=$gawk->user['remote']['nmControl/openControl']['response']['data']['config'][$st['level']]['add']['rescap'][0];
	$st['res']['add'][1]=$gawk->user['remote']['nmControl/openControl']['response']['data']['config'][$st['level']]['add']['rescap'][1];
	$st['res']['add'][3]=$gawk->user['remote']['nmControl/openControl']['response']['data']['config'][$st['level']]['add']['rescap'][3];
	return $st;
}
function get_members(){
	global $gawk,$alliance_name;
	$vars=array("ex_data"=>array("alliance_name"=>$alliance_name));
	$gawk->R_Remote('nmAlliance/getMemberList',$vars);
	$all_members="";
	foreach ($gawk->user['remote']['nmAlliance/getMemberList']['response']['data']['members'] as $member){
		$all_members.=$member['user_id'].",";
	}
	$all_members=trim ($all_members,",");
	$sql="select user_id from users where type in (7,11,12) and user_id in ($all_members);";
	$res=pg_query($gawk->db,$sql);
	$resf=pg_fetch_all($res);
	$users=array();
	if (is_array($resf[0])){
		foreach ($resf as $x){
			$users[]=$x['user_id'];
		}
	}
	return $users;
}
function put_res_to_station(){
	// добавить проверку на 24 часа
	global $gaw,$mults,$station;
	$resids=array(0,1,3);
	foreach ($mults as $mid){
		$gaw->G_InitId($mid);
		$gaw->G_Login();
		$gaw->R_Remote('nmControl/openDonate',array("ex_data"=>array("planet_id"=>$gaw->user['mother'])));
		$userdonate=false;
		foreach ($gaw->user['remote']['nmControl/openDonate']['response']['data']['recodes'] as $rc){
			if ($rc['user_id']==$gaw->user['game_data']['user_id']){
				$userdonate=true;
				break;
			}
		}
		if (!$userdonate){
			$rescurr=array();
			$gaw->G_Ping();
			$rescurr[0]=$gaw->user['remote']['nmUser/getGameDataEx']['response']['data']['res_info'][0]['now'];
			$rescurr[1]=$gaw->user['remote']['nmUser/getGameDataEx']['response']['data']['res_info'][1]['now'];
			$rescurr[3]=$gaw->user['remote']['nmUser/getGameDataEx']['response']['data']['rutile'];
			foreach ($resids as $resid){
				if ($rescurr[$resid]<$station['res']['add'][$resid])
					$res=$rescurr[$resid];
				else
					$res=$station['res']['add'][$resid];
				$vars=array("ex_data"=>array(
					"res_id"=>$resid,
					"planet_id"=>$gaw->user['mother'],
					"res_count"=>$res
				));
				$gaw->R_Remote('nmControl/donate',$vars);
			}
		}else{
			$gaw->G_Log(G_INFO,"already donate");
		}
	}
}
function up_station(){
	global $gawk,$station;
	//check if res enough for up
	$station=get_station_details();
	if (
		($station['res']['cur'][0]==$station['res']['up'][0])
		and($station['res']['cur'][1]==$station['res']['up'][1])
		and($station['res']['cur'][3]==$station['res']['up'][3])
	){
		$gawk->R_Remote('nmControl/upgrade');//поднять уровень станции если есть ресы
	}
	$station=get_station_details();
}
function up_tec(){
	global $gawk,$station;
	$return=array("tec_id"=>'-1',"time"=>-1,"level"=>-1);
	//get current tech
	$station=get_station_details();
	$gawk->R_Remote('nmAllianceTec/getTecsInfo');
	//find active tech return id
	$tec_id_new=1001;
	if (isset($gawk->user['remote']['nmAllianceTec/getTecsInfo']['response']['data']['tecs'][$tec_id_new]['level']))
		$tec_level_new=$gawk->user['remote']['nmAllianceTec/getTecsInfo']['response']['data']['tecs'][$tec_id_new]['level'];
	else
		$tec_level_new=-1;
	if(count($gawk->user['remote']['nmAllianceTec/getTecsInfo']['response']['data']['tecs'])>0){
		foreach ($gawk->user['remote']['nmAllianceTec/getTecsInfo']['response']['data']['tecs'] as $tec_id=>$tec_info){
			if ($tec_info['state']==1){
				$return=array(
					"tec_id"=>$tec_id,
					"level"=>$tec_info['level'],
					"time"=>$tec_info['time']
				);
				return $return;
				break;
			}
			if ($tec_info['level']<$tec_level_new){
				$tec_id_new=$tec_id;
				$tec_level_new=$tec_info['level'];
			}
		}
	}
	// up tec 5kk/4kk/20k
	if (($station['res']['cur'][0]>=5000000)and($station['res']['cur'][1]>=4000000)and($station['res']['cur'][3]>=20000)){
		//nmAllianceTec/upgrade "tec_id":1001 (1002,1003,1004) - повысить техи (разобраться как посмотреть)	
		$vars=array("ex_data"=>array("tec_id"=>$tec_id_new));
		$gawk->R_Remote('nmAllianceTec/upgrade',$vars);
		if ($gawk->user['remote']['nmAllianceTec/upgrade']['response']['data']['error']!=0){
			return false;
		}
		$return=array(
			"tec_id"=>$tec_id_new,
			"level"=>$gawk->user['remote']['nmAllianceTec/upgrade']['response']['data']['tec']['level'],
			"time"=>$gawk->user['remote']['nmAllianceTec/upgrade']['response']['data']['tec']['time']
		);
		return $return;
	}
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
function tec_speedup(){
	global $gaw,$tec,$speedup;
	//Ускоряем газом
	$gaw->R_Remote("nmAllianceTec/getTecsInfo");
	if ($gaw->user['remote']['nmAllianceTec/getTecsInfo']['response']['data']['spu_count']==0){
		$vars=array("ex_data"=>array("planet_id"=>$gaw->user['mother'],"tec_id"=>$tec['tec_id'],"item_id"=>-1));
		$gaw->R_Remote("nmAllianceTec/speedUp",$vars);
	}
	//Ускоряем ускорителями в наличии
	//Возможна потенциальная проблема когда газом ускорить неполучилось по причине его отсутсвия, а ускорители не принимает
	// ускоряем только от масимума до минимума, в этом случае можем иметь ситуацию когда остаток исследования небольшой, эту погрешность пускай остаётся
	for ($i=8;$i>=1;$i--){
		$user_items=get_user_speed_items();
		if ($user_items[$i]>0){
			for ($k=1;$k<=$user_items[$i];$k++){
				$tec=up_tec();
				if ($tec['time']>$speedup[$i]){
					$vars=array("ex_data"=>array("planet_id"=>$gaw->user['mother'],"tec_id"=>$tec['tec_id'],"user_data"=>array("goods_id"=>$i),"item_id"=>$i));
					$gaw->R_Remote("nmAllianceTec/speedUp",$vars);
				}else
					break;
			}
		}
	}
}
function user_in_alliance(){
	global $gaw,$alliance_name;
	if ($alliance_name==$gaw->user['remote']['nmUser/getGameDataEx']['response']['data']['alliance_name'])
		return true;
	else
		return false;
}
function user_leave_alliance(){
	global $gaw;
	// check if user doesn`t have kvarc & speedups then leave, otherwise stay
	$rutile=$gaw->user['remote']['nmUser/getGameDataEx']['response']['data']['rutile'];
	$speed=get_user_speed_items();
	$is_speed=false;
	for ($i=1;$i<=8;$i++){
		if ($speed[$i]>0){
			$is_speed=true;
			break;
		}
	}
	if (($is_speed==false)and($rutile==0)){
		$vars=array("ex_data"=>array("alliance_name"=>$alliance_name));
		$gaw->R_Remote('nmAlliance/exitAlliance',$vars);
		pg_query($gaw->db,"insert into bots.bot12_status values (".$gaw->user['game_data']['user_id'].",true) on conflict (user_id) do update set is_finished=true;");
	}
}
function send_join_request_to_alliance(){
	global $gaw,$alliance_name;
	$gaw->R_Remote('nmAlliance/joinAlliance',array("ex_data"=>array("alliance_name"=>$alliance_name)));
}
function apply_join_to_alliance(){
	global $gawk,$gaw,$alliance_name;
	$vars['ex_data']=array("user_id"=>$gaw->user['game_data']['user_id'],"alliance_name"=>$alliance_name);
	$gawk->R_Remote('nmAlliance/agreeApply',$vars);
}
/*  END FUNCTIONS   */

/* BEGIN MAIN*/
$gawk=new GAW();
$gaw=new GAW();
$gawk->G_InitId($commander_id);
$gawk->G_Login();
$alliance_name=$gawk->user['remote']['nmUser/getGameDataEx']['response']['data']['alliance_name'];
$station=get_station_details();
$tecid=-1;
//nmControl/donate "res_id":0,"planet_id":"9_340_2","res_count":450000 - закинуть ресы на станцию (0,1,3)
$mults=get_members();
put_res_to_station();//скидываем ресы на станцию с мультов
up_station();//подымаем уровень станции если это возможно, за 1 раз подымаем только 1 уровень
/// проходим по мультам которые есть в альянсе
foreach ($mults as $mid){
	$gaw->G_InitId($mid);
	$gaw->G_Login();
	$tec=up_tec();// подымаем тех на станции и возвращаем id того теха который сейчас качается
	if ($tec==false){
		//поднять тех не получилось, значит ресов недостаточно, выходим
		$gawk->G_Log(G_NOTICE,"cannot up tec, probably not enough res, exiting");
		die();
	}
	if (user_in_alliance()){// в противном случае этот игрок не в альянсе и его попросту игнорируем
		tec_speedup();
		user_leave_alliance();
	}
}

// закидываем новых мультов в альянс
// получаем список свободных мест в альянсе
$gawk->R_Remote('nmAlliance/getAllianceInfo',array("ex_data"=>array("alliance_name"=>$alliance_name)));
$free=49-$gawk->user['remote']['nmAlliance/getAllianceInfo']['response']['data']['member_count'];
if ($free>0){
	// закидываем новых мультов, стараемся закидывать тех у кого больше всего ускорителей и тех которые ещё небыли в альянсе
	$sql="select u1.user_id,(item->>'1')::integer*5*60+(item->>'2')::integer*15*60+(item->>'3')::integer*30*60+(item->>'4')::integer*3600+(item->>'5')::integer*3*3600+(item->>'6')::integer*8*3600+(item->>'7')::integer*15*3600+(item->>'8')::integer*24*3600 as s from users  as u1 left join userinfo as d1 on (u1.user_id=d1.user_id) left join bots.bot12_status as b1 on (u1.user_id=b1.user_id) where u1.ban=false and u1.enabled=true and type in (7,11,12) and item is not null and item->>'1'!='' and (b1.is_finished!=true or is_finished is null) order by s desc limit $free;";
	$res=pg_query($gawk->db,$sql);
	$resf=pg_fetch_all($res);
	foreach ($resf as $newu){
		$gaw->G_InitId($newu['user_id']);
		$gaw->G_Login();
		send_join_request_to_alliance();
		apply_join_to_alliance();
	}
}

/* END MAIN */
?>
