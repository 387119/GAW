<?php

# непомню но вроде этот бот собирает ресы на основную планку и отправляет их в сейв
/*
	todo
	- немогу решить как засейвить основную планку пока грузы летают за ресами, 2 варианта
		- поднять вместе с грузами на время максимального полета туда и назад за ресами (надо расчитывать время полёта, как незнаю.)
		- ждать и подымать только в случае атаки (тут авто подьём надо реализовать, где и как пока незнаю)
 обнаруженные бока в процессе эксплуатации
 1 - если нет сейверов в друзьях то сейв уйдёт но не продлится.

*/
include "../lib/gaw.php";
function inlog ($text){
	file_put_contents("../log/finish.log",$text."\n",FILE_APPEND);
}
function comment($text){
	global $gaw;
	pg_query ($gaw->db,"update bot1 set comment='".$text."',last_update=now() where user_name='".$gaw->user['user_name']."';");
}
$user=$argv[1];
if (isset($argv[2]))
	$keep_res=$argv[2];// 0 - save with res, 1 - save without res
else
	$keep_res=0;
// вывалилось с ошибкой когда в сейве было недостаточно грузов даже для рассылки по планкам

// Предполагается что на основе есть хотябі минимально необходимое количество грузовиков для перекачки ресов, недостающие будут достраиваться по тихоньку

//Initialization 
//$gaw=new GAW("login_name","login_id","user_name","user_id","pass_clear","pass_hash");
switch ($keep_res){
	case "0":
		$save_with_res=true;
		$send_to_save=true;
		break;
	case "1":
		$save_with_res=false;
		$send_to_save=true;
		break;
	default:
		$save_with_res=true;
		$send_to_save=true;
		break;
}
#$gaw=new GAW("387119","ElMar");

#$gaw=new GAW("Partizanka8","jI2n6k5O","Хз до");
#$gaw->user["planets_for_work"]=array("187_445_8","187_445_13","187_445_4","187_447_4","187_441_15");

#$gaw=new GAW("Partizanka8","Arkady");
#$gaw->user["planets_for_work"]=array("187_444_6","187_444_11","187_444_12","187_444_5","187_444_15","187_446_4");
// check if user is online
inlog("START: $user / $keep_res / ".Date("c"));
#$status=exec ("./get_status.sh '${user}'");
#if ($status!="offline"){
#	inlog("BREAK: $user / $keep_res / user is online /".Date("c"));
#	die(1);
#}
$gaw=new GAW($user);
pg_query ($gaw->db,"insert into bot1 (user_name) values ('$user') on conflict (user_name) do nothing;");
#$gaw->user["planets_for_work"]=array("187_442_13","187_442_10","187_442_14","187_445_5","187_446_14");

#$gaw=new GAW("mamed","sobstvenostala","MAMED");
#$gaw->user["planets_for_work"]=array("179_441_1","179_442_5","179_441_9","179_441_3","179_441_4");

#$gaw=new GAW("mamed","sobstvenostala","G20529864S15");
#$gaw->user["planets_for_work"]=array("179_441_5","179_441_15","179_441_2","179_441_7","179_441_12");

//Василий Новиков
//Cat_of_the_Rio

#$gaw=new GAW("Vasjamba","Василий Новиков");
#$gaw->user["planets_for_work"]=array("174_394_4","174_396_3","174_397_3","174_397_4","174_397_5","174_397_8","174_399_15");
#$gaw=new GAW("Vasjamba","Cat_of_the_Rio");
#$gaw->user["planets_for_work"]=array("174_393_8","174_393_9","174_395_6","174_395_11","174_395_13","174_395_14");
#$gaw=new GAW("Vasjamba","Stornado320");
#$gaw->user["planets_for_work"]=array("174_403_1","174_404_7","174_405_4","174_405_7","174_405_13","174_407_6");

#$gaw=new GAW("","","","","","");
#$gaw->user["planets_for_work"]=array("");


/// добавить проверку планок которіе надо обрабатівать

$gaw->G_login();
#$status=exec ("./set_bot1.sh '${user}'");
#$gaw->R_getAllInfo();
#$gaw->R_auto_login();
#$gaw->R_getUserList();
#$gaw->R_enterGame();
#$gaw->G_updatePlanetsInfo("all",0);
#$gaw->G_Spacecraft("all");
#$gaw->G_Save(false);
#$gaw->G_Sleep(10800);
#$gaw->R_getRadarFleets();
#$gaw->R_getFrientList();
#print_r($gaw->user);
#die();
#$test->R_getUniverse (10,10);
/*
	1 - флот летает, ресов нет +
	2 - флот летает, ресы есть
	3 - флот летит назад, ресы есть
	4 - флот на планке, ресы на других есть
	5 - флот на планке, ресов на других нет
	6 - флоты летят на другие планки
	7 - флоты на других планках
	8 - флоты летят назад
	9 - флот на материнке, ресов нет
*/
/*
	добавить список проверяемых планок
*/
//take info about planets
$gaw->G_updatePlanetsInfo("all",0);//update info about planets 1 - array or string, array('12_12_12','14_14_5'), or string "all", second - after how many seconds need update data, 0 - now
$mother=
	$gaw->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][0]."_".
	$gaw->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][1]."_".
	$gaw->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][2];
//check if enough resources on planets
$res_max=1000000;
$gaz_max=200000;
$res_max_taked=true;

// подготавливаем список планет для обхода.
echo Date("c")." определяем список планет для обхода\n";
#exec("./set_comment.sh '".$gaw->user["user_name"]."' 'подготовка'");
echo "main planet: ".$mother."\n";
foreach ($gaw->user["planets"] as $planet => $data){
	$to_work=false;
	if (($data["info"]["data"]["build"]["7"]["lv"]>=8)or($data["info"]["data"]["build"]["8"]["lv"]>=8)or($data["info"]["data"]["build"]["9"]["lv"]>=8))
		$to_work=true;
	if ($to_work==true){
		if (
			($data["info"]["data"]["skin_id"]==56)or
			($data["info"]["data"]["build"]["12"]["lv"]==1)or
			($data["info"]["data"]["spacecraft"]["10"]["count"]==8)or
			($mother==$planet)
		)
		$to_work=false;
	}
	if ($to_work==true){
		$gaw->user["planets_for_work"][]=$planet;
	}else{
		echo "skip $planet\n";
	}
}

echo  Date("c")." проверяем достаточно ли лежит на планках\n";
foreach ($gaw->user["planets"] as $key => $val){
	if (($mother != $key)and(in_array($key,$gaw->user["planets_for_work"]))){
		$res=intval($val["info"]["data"]["res"][0]["now"]+$val["info"]["data"]["res"][1]["now"]+$val["info"]["data"]["res"][2]["now"]);
		$gaz=intval($val["info"]["data"]["res"][2]["now"]);
		$res_str=intval($val["info"]["data"]["res"][0]["now"])."/".intval($val["info"]["data"]["res"][1]["now"])."/".intval($val["info"]["data"]["res"][2]["now"]);
		echo "$key $res $res_str\n";
		if (($res<$res_max)and($gaz<$gaz_max)){
			$res_max_taked=false;
		}
	}
}
if ($res_max_taked==false){
	inlog("STOP: $user / not enought for start / ".Date("c"));
	die(1);
}
#exec("./set_comment.sh '".$gaw->user["user_name"]."' 'возврат сейва'");
comment('возврат сейва');

echo  Date("c")." если достаточно то возвращаем сейв материнской планки\n";
//при перезапуске произошёл возврат летящих грузов на другие планки, так как они посчитались как будто в сейве
$gaw->R_getAllInfo();//check all fleets
if ($gaw->user["remote_last_results"]["R_getAllInfo"]["data"]["error"]==0){
	foreach ($gaw->user["remote_last_results"]["R_getAllInfo"]["data"]["fleet"] as $val){
		$pos=$val["from"][0]."_".$val["from"][1]."_".$val["from"][2];
		if ($mother==$pos){
			if ($val["purpose"]!=1){
				if ($res_max_taked==true){
					echo "put back fleet id ".$val["fleet_uid"]."\n";
					$gaw->R_cancelFleet($val['fleet_uid']);
				}
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
				if ($mother==$pos){
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

echo Date("c")." проверка что флотов с материнской планки нет в сейве\n";
$fleetisback=true;
if ($gaw->user["remote_last_results"]["R_getAllInfo"]["data"]["error"]==0){
	foreach ($gaw->user["remote_last_results"]["R_getAllInfo"]["data"]["fleet"] as $val){
		if ($val["from"][0]."_".$val["from"][1]."_".$val["from"][2]==$mother){
			$fleetisback=false;
			break;
		}
	}
}

//get info from spacecraft (to get count of supercargo and big cargo)

//$gaw->R_getAllInfo();//check all fleets again - send only there where to need

/// ADD CHECK IF NEED FLEET TO BE SENDED TO EACH PLANET
#exec("./set_comment.sh '".$gaw->user["user_name"]."' 'отправка грузов'");
comment('отправка грузов');

echo  Date("c")." отправка грузов на удаленные планки и ожидание их долёта\n";
#while (true){
	// нет проверки того что уже туда может чтото лететь, в єтом случае может быть попытка отправки повторно
	// нет проверки что ресі уже могут лелеть назад
	if ($fleetisback==false)
		break;
	$gaw->R_getSpacecraft($mother);
	echo "cargos on main planet: supercargo-".$gaw->user["planets"][$mother]["spacecraft"]["data"][22]." cargo-".$gaw->user["planets"][$mother]["spacecraft"]["data"][1]."\n";
	$waittime=-1;
	//calculate and send fleets for to other planets
	$cargo_super=$gaw->user["remote_last_results"]["R_getSpacecraft"]["data"]["data"][22];
	$cargo_big=$gaw->user["remote_last_results"]["R_getSpacecraft"]["data"]["data"][1];
	foreach ($gaw->user["planets"] as $key => $val){
		if (($mother!=$key)and(in_array($key,$gaw->user["planets_for_work"]))){
			//check if exists cargos will be enough
			$gaw->R_getSpacecraft($key);
			$cargo_res_exists=($gaw->user["planets"][$key]["spacecraft"]["data"][1]*25000)+($gaw->user["planets"][$key]["spacecraft"]["data"][22]*75000);
			$res=intval($val["info"]["data"]["res"][0]["now"]+$val["info"]["data"]["res"][1]["now"]+$val["info"]["data"]["res"][2]["now"]);
			$gaz=intval($val["info"]["data"]["res"][2]["now"]);
			if (($res>=$res_max)or($gaz>=$gaz_max)){
				if ($cargo_res_exists<$res){
					$res=$res-$cargo_res_exists;
					$need_super_cargo=intval($res/75000)+10;
					if ($need_super_cargo>$cargo_super){
						$need_super_cargo=$cargo_super;
						$need_big_cargo=intval((($res-($need_super_cargo*75000))/25000)+25);
					}
					else $need_big_cargo=0;
					$planets["to"]=$key;
					$planets["from"]=$mother;
					$ares=array();
					$ships=array("22"=>$need_super_cargo,"1"=>$need_big_cargo);
					echo "$key $res $cargo_res_exists $need_super_cargo $need_big_cargo\n";
					$gaw->R_sentFleet(7,$planets,$ares,$ships);
					$cargo_super=$cargo_super-$need_super_cargo;
					$cargo_big=$cargo_big-$need_big_cargo;
					if ($waittime<$gaw->user["remote_last_results"]["R_sentFleet"]["data"]["time"])
						$waittime=$gaw->user["remote_last_results"]["R_sentFleet"]["data"]["time"];
				}
			}
		}
	}
	if ($waittime==-1)
		break;
	/*
	if ($waittime>0){
		exec("./set_comment.sh '".$gaw->user["user_name"]."' 'Постройка грузов на лишние ресы'");
		// дупля не кину как проверку сделать на необходимость выполнения данного пункта, надо подумать
		if (($res_max_taked==true)and($save_with_res==true)and($send_to_save==true)){
			//отработает пока только при правильной первой отработки скрипта
			echo Date("c")." постройка грузов при необходимости\n";
			$gaw->G_updatePlanetsInfo(array($mother),0);
			$gaw->R_getSpacecraft($mother);
			$res=$gaw->user["planets"][$mother]["info"]["data"]["res"][0]["now"]+
				$gaw->user["planets"][$mother]["info"]["data"]["res"][1]["now"]+
				$gaw->user["planets"][$mother]["info"]["data"]["res"][2]["now"];
			$res=intval($res);
			$cargo_res=($gaw->user["planets"][$mother]["spacecraft"]["data"]["22"]*75000)+
				($gaw->user["planets"][$mother]["spacecraft"]["data"]["1"]*25000)+
				($gaw->user["planets"][$mother]["spacecraft"]["data"]["23"]*40000);
			$cargo_res=intval($cargo_res);
			echo "на планке $res ресов, можно увезти $cargo_res\n";
			if ($res>$cargo_res){
				$newcargo=(($res-$cargo_res)/2)/6000;
				$delta=intval($newcargo/10);
				if ($delta<=1)$delta=10;
				$newcargo=intval($newcargo+$delta);
				$gaw->R_product($mother,$newcargo,1);
				echo "need create $newcargo with delta $delta\n";
			}
		}
	*/	
		
	echo "send main cargos to save \n";
	#exec("./set_comment.sh '".$gaw->user["user_name"]."' 'отправка сейва на время сбора...'");
	comment('отправка сейва на время сбора...');
	$gaw->G_updatePlanetsInfo(array($mother),0);
	$gaw->R_getSpacecraft($mother);
	$res=$gaw->user["planets"][$mother]["info"]["data"]["res"][0]["now"]+
		$gaw->user["planets"][$mother]["info"]["data"]["res"][1]["now"]+
		$gaw->user["planets"][$mother]["info"]["data"]["res"][2]["now"];
	$res=intval($res);
	$cargo_res=($gaw->user["planets"][$mother]["spacecraft"]["data"]["22"]*75000)+
		($gaw->user["planets"][$mother]["spacecraft"]["data"]["1"]*25000)+
		($gaw->user["planets"][$mother]["spacecraft"]["data"]["23"]*40000);
	$cargo_res=intval($cargo_res);
	echo "на планке $res ресов, можно увезти $cargo_res\n";
	$gaw->G_Save();
	#exec("./set_comment.sh '".$gaw->user["user_name"]."' 'ожидание долёта грузов'");
	comment('ожидание долёта грузов');
	echo "sleep $waittime\n";
	$gaw->G_sleep($waittime);
	$gaw->R_cancelFleet($gaw->user['last_fleet_save']['fleet_uid']);
#}
//come back from save main cargos
//echo "cargo enough, we can back them to mother";
$gaw->G_updatePlanetsInfo("all",0);
$gaw->R_getAllInfo();

#exec("./set_comment.sh '".$gaw->user["user_name"]."' 'возврат грузов'");
comment('возврат грузов');
echo  Date("c")." возвращаем все на материнку если там ресов больше чем макс\n";
foreach ($gaw->user["planets"] as $key => $val){
	if ($fleetisback==false)
		break;
	//нет проверки что текущих ресов может біть больше чем ожидалось, в єтом случае надо частично раздробить мет и крис
	if (($mother!=$key)and(in_array($key,$gaw->user["planets_for_work"]))){
		$res_sum=$gaw->user["planets"][$key]["info"]["data"]["res"][0]["now"]+$gaw->user["planets"][$key]["info"]["data"]["res"][1]["now"]+$gaw->user["planets"][$key]["info"]["data"]["res"][2]["now"];
		$gaz_sum=$gaw->user["planets"][$key]["info"]["data"]["res"][2]["now"];
		if (($res_sum>$res_max)or($gaz_sum>$gaz_max)){
			$gaw->R_getSpacecraft($key);
			$planets["to"]=$mother;
			$planets["from"]=$key;
			$gas=$gaw->user["planets"][$key]["info"]["data"]["res"][2]["now"]-50000;
			if ($gas<0)$gas=0;
			$res=array(
				"0"=>$gaw->user["planets"][$key]["info"]["data"]["res"][0]["now"],
				"1"=>$gaw->user["planets"][$key]["info"]["data"]["res"][1]["now"],
				"2"=>$gas
			);
			$ships=array("22"=>$gaw->user["planets"][$key]["spacecraft"]["data"][22],"1"=>$gaw->user["planets"][$key]["spacecraft"]["data"][1]);
			$gaw->R_sentFleet(7,$planets,$res,$ships);
		}
	}
}

echo  Date("c")." ожидание возврата\n";
#while (true){
	#if ($fleetisback==false)
	#	break;
	$gaw->R_getAllInfo();
	$waittime=0;
	if ($gaw->user["remote_last_results"]["R_getAllInfo"]["data"]["error"]==0){
		foreach ($gaw->user["remote_last_results"]["R_getAllInfo"]["data"]["fleet"] as $val){
			if (($val["purpose"]==7)or($val["purpose"]==1))
				if ($val["time"]>$waittime)
					$waittime=$val["time"];
		}
		echo "fleets will come in $waittime seconds, waiting...\n";
	}
	if ($waittime>0) 
		$gaw->G_sleep ($waittime+10);
	else 
		break;
#}

/// SAVE!!!!
if ($send_to_save==true){
	$gaw->G_updatePlanetsInfo(array($mother),0);
	$gaw->R_getSpacecraft($mother);
	comment('отправка в сейв...');
	#exec("./set_comment.sh '".$gaw->user["user_name"]."' 'отправка в сейв...'");
	$gaw->G_Save();
	#exec("./set_comment.sh '".$gaw->user["user_name"]."' '".time()."'");
	#exec("./set_res.sh '".$gaw->user["user_name"]."' '"
	#	.intval($gaw->user['last_fleet_save']['res'][0]/1000000)."_"
	#	.intval($gaw->user['last_fleet_save']['res'][1]/1000000)."_"
	#	.intval($gaw->user['last_fleet_save']['res'][2]/1000000)." "
	#	.$gaw->user['last_fleet_save']['total_res']
	#	."' "
	#	.$gaw->user['last_fleet_save']['total_percent']);
	pg_query($gaw->db,"update bot1 set last_save=now(),comment='Завершено',res0=".intval($gaw->user['last_fleet_save']['res'][0]).",res1=".intval($gaw->user['last_fleet_save']['res'][1]).",res2=".intval($gaw->user['last_fleet_save']['res'][2]).",resmax=".intval($gaw->user['last_fleet_save']['res_max'])." where user_name='".$gaw->user['user_name']."';");
// set finish to log
}
inlog("STOP: $user / $keep_res / ".Date("c"));
#echo "--------------------\n";
#print_r ($gaw->user);
?>

