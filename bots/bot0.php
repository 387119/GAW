<?php
/*
problems
1 - если подняли флот при атаке и перезапустили скрипт то флот не опуститься так как не будет переменной save_uid
2 - подарки собираются только 1 день работы, а потом нет, видимо некая переменная не сбрасывается
*/
function distance ($planet1,$planet2){
	$ap1=preg_split("/_/",$planet1);
	$ap2=preg_split("/_/",$planet2);
	$weight1=(($ap1[0]-1)*600)+$ap1[1];
	$weight2=(($ap2[0]-1)*600)+$ap2[1];
	$weight=abs($weight1-$weight2);
	return $weight;
}
function get_save_planets($planets_list){
	$res=array();
	foreach($planets_list as $planet){
		if ($planet["skin_id"]==57)
			$res[]=$planet["position"]["0"]."_".$planet["position"]["1"]."_".$planet["position"]["2"];
	}
	sort($res);
	return $res;
}
include "../lib/gaw.php";
#207682044
$userid=$argv[1];
#$userid=207682044;
$gaw=new GAW();
$gaw->G_InitId($userid);
$save_uid=array();
$save_when=300;
$gaw->G_login();
//$gaw->R_Remote('nmUniverse/getUniverse',array("ex_data"=>array("planet_id"=>-1,"sid"=>"1","language"=>"ru","gid"=>"1")));
$gaw->G_updatePlanets(false,6);
// Поменять получение планет не из базы а реально
//$planets=array();
//$res=pg_query($gaw->db,"select position from planets where user_name='".$user."' and skin_id=57 order by position;");
//$resf=pg_fetch_all($res);
//foreach($resf as $v)
//	$planets[]=$v['position'];
//******
$planets=get_save_planets($gaw->user["remote"]["nmUser/getUserPlanetList"]["response"]["data"]["planets"]);
$gaw->R_Remote('nmFleet/getRadarFleets');
$last_attack=true;
$last_attack_check_time=0;
$last_check_friends=0;
while (true){
	// **** AUTO UP FLEET IN ATTACK
	if (($last_attack==true)or($last_attack_check_time>30)){
		//$gaw->R_getRadarFleets();
		$gaw->R_Remote("nmFleet/getRadarFleets");
		if (count($gaw->user["remote"]["nmFleet/getRadarFleets"]["response"]["data"]["fleet"])==0)
			$last_attack=false;
		else
			$last_attack=true;
		if ($last_attack==true){
			// we see some attacks, check them, if we see some attack less than 30 sec then up fleet nad put it down only after finish
			$min_attack_time=10000;
			$attaked_planet="";
			foreach($gaw->user["remote"]["nmFleet/getRadarFleets"]["response"]["data"]["fleet"] as $fleet){
				if ($fleet["time"]<$min_attack_time){
					$min_attack_time=$fleet["time"];
					$attaked_planet=$fleet["target"][0]."_".$fleet["target"][1]."_".$fleet["target"][2];
					$pirates=$fleet["target"][0]."_".$fleet["target"][1]."_17";
				}
			}
			echo "attack in ".$min_attack_time."\n";
			//some attack less than 30 sec, save SD if its possible
			if ($min_attack_time<$save_when){
				$gaw->G_updatePlanets($attaked_planet,2);
				if ($gaw->user["planets"][$attaked_planet]["info"]["data"]["spacecraft"][9]["count"]>0){
					$save_ships[9]=$gaw->user["planets"][$attaked_planet]["info"]["data"]["spacecraft"][9]["count"];
					$gas=intval($gaw->user["planets"][$attaked_planet]["info"]["data"]["res"][2]["now"]-1000);
					if ($gas<0)$gas=0;
					$res=array("0"=>0,"1"=>0,"2"=>$gas);
					//$gaw->R_sentFleet(8,$planets,$res,$save_ships);
					$ex_data=array("end_pos"=>$pirates,"purpose"=>8,"upshift"=>1,"bring_res"=>$res,"bring_ship"=>$save_ships,"rate"=>100,"start_pos"=>$attaked_planet);
					$gaw->R_Remote("nmFleet/sentFleet",array("ex_data"=>$ex_data));
#					print_r($gaw->user);
					$save_uid[$attaked_planet]=$gaw->user["remote"]["nmFleet/sentFleet"]["response"]["data"]["fleet_uid"];
					echo Date("c")." save fleet on $attaked_planet due attack less than 30 sec (uid: ".$save_uid[$attaked_planet].")\n";
				}
			}
		}
		else{
			foreach($save_uid as $planet=>$fleet_uid){
				echo Date("c")." come back fleet on planet $planet (uid: $fleet_uid)\n";
				//$gaw->R_cancelFleet($fleet_uid);
				$gaw->R_Remote("nmFleet/cancelFleet",array("ex_data"=>array("fleet_uid"=>$fleet_uid)));
				unset($save_uid[$planet]);
			}
		}
		$last_attack_check_time=0;
	}
	// ***** SAVE PROCEDURE
	$gaw->R_Remote('nmFleet/getInviteUnionFleets');
	//get presistent ships
	if (count($gaw->user["remote"]["nmFleet/getInviteUnionFleets"]["response"]["data"]["fleet"])>0){
		$gaw->G_updatePlanets($planets,2);
		$ships=array();
		foreach($planets as $planet){
			$ships[$planet]=$gaw->user["planets"][$planet]["info"]["data"]["spacecraft"][9]["count"];
			if (!is_numeric($ships[$planet]))
				$ships[$planet]=0;
		}
		// look requests
		foreach ($gaw->user["remote"]["nmFleet/getInviteUnionFleets"]["response"]["data"]["fleet"] as $fleet){
			$planet_to=$fleet["target"][0]."_".$fleet["target"][1]."_".$fleet["target"][2];
			$distance=-1;
			$save_from="";
			//find better planet for save
			foreach($planets as $planet){
				$new_distance=distance($planet,$planet_to);
				if (($distance<$new_distance)and($ships[$planet]>0)){
					$distance=$new_distance;
					$save_from=$planet;
				}
			}
			//save and come back
			if ($save_from != ""){
				echo "save planet ".$planet_to.", from ".$save_from.", gaz on planet ".$gaw->user['planets'][$save_from]['info']['data']['res']['2']['now']."\n";
				//$gaw->R_agreeUnionFleet($save_from,$fleet["fleet_uid"]);
				$ex_data=array("planet_id"=>$save_from, "upshift"=>0, "bring_ship"=>array("9"=>1), "rate"=>10, "fleet_uid"=>$fleet["fleet_uid"]);
				$gaw->R_Remote("nmFleet/agreeUnionFleet",array("ex_data"=>$ex_data));
				//$gaw->R_cancelFleet($fleet["fleet_uid"]);
				$gaw->R_Remote("nmFleet/cancelFleet",array("ex_data"=>array("fleet_uid"=>$fleet["fleet_uid"])));
				$ships[$save_from]=$ships[$save_from]-1;
			}
		}
	}
	// **** ACCEPT NEW FRIEND
	//$gaw->R_getRequestList();
	$gaw->R_Remote("nmFriendEx/getRequestList");
	if (count($gaw->user["remote"]["nmFriendEx/getRequestList"]["response"]["data"]["request"])>0){
		foreach($gaw->user["remote"]["nmFriendEx/getRequestList"]["response"]["data"]["request"] as $request){
			$gaw->R_Remote('nmFriendEx/acceptFriend',array("ex_data"=>array("target_id"=>$request["user_id"])));
		}
	}
	// **** CHECK FRIENDS ONLINE every 30 sec and update statuses on wiki
	// need update, need update status here (read from wiki old), and update wiki only in change
	if ($last_check_friends>=30){
		//check online and update wiki
		$gaw->R_Remote('nmFriend/getFrientList');
		foreach($gaw->user["remote"]["nmFriend/getFrientList"]["response"]["data"]["friends"] as $dd=>$friend){
			if (isset($friend["heart_type"])){
				$stat_online="false";
				switch ($friend["heart_type"]){
					case "s":
						$stat_online="true";
						$stat_sec=$friend["heart_value"];
						break;
					case "m":
						$stat_sec=$friend["heart_value"]*60;
						break;
					case "h":
						$stat_sec=$friend["heart_value"]*3600;
						break;
					case "d":
						$stat_sec=$friend["heart_value"]*86400;
						break;
				}
				pg_query($gaw->db,"insert into bot0 (user_id,online,offline_seconds,last_update) values (".$friend["user_id"].",".$stat_online.",$stat_sec,CURRENT_TIMESTAMP) on conflict (user_id) do update set online=$stat_online,offline_seconds=$stat_sec,last_update=CURRENT_TIMESTAMP;");
			}
		}
		$last_check_friends=0;
	echo Date("c")." db status updated\n";
	}
	// **** WAITING PERIOD 10 sec
	$gaw->G_Sleep(10);
	$last_attack_check_time+=10;
	$last_check_friends+=10;
}
?>

