<?php
/*
problems
1 - если подняли флот при атаке и перезапустили скрипт то флот не опуститься так как не будет переменной save_uid
2 - подарки собираются только 1 день работы, а потом нет, видимо некая переменная не сбрасывается
*/
include "../lib/gaw.php";
$user='aurum911';
$gaw=new GAW($user);
#$gaw=new GAW("387119","ElNat");
$planets=array();
$res=pg_query($gaw->db,"select position from planets where user_name='".$user."' and skin_id=57 order by position;");
$resf=pg_fetch_all($res);
foreach($resf as $v)
	$planets[]=$v['position'];
$save_uid=array();
$save_when=30;
$gaw->G_login();
$gaw->R_getRadarFleets();
function distance ($planet1,$planet2){
	$ap1=preg_split("/_/",$planet1);
	$ap2=preg_split("/_/",$planet2);
	$weight1=(($ap1[0]-1)*600)+$ap1[1];
	$weight2=(($ap2[0]-1)*600)+$ap2[1];
	$weight=abs($weight1-$weight2);
	return $weight;
}
$last_attack=true;
$last_attack_check_time=0;
$last_check_friends=0;
while (true){
	// **** AUTO UP FLEET IN ATTACK
	if (($last_attack==true)or($last_attack_check_time>120)){
		$gaw->R_getRadarFleets();
		if (count($gaw->user["remote_last_results"]["R_getRadarFleets"]["data"]["fleet"])==0)
			$last_attack=false;
		else
			$last_attack=true;
		if ($last_attack==true){
			// we see some attacks, check them, if we see some attack less than 30 sec then up fleet nad put it down only after finish
			$min_attack_time=10000;
			$attaked_planet="";
			foreach($gaw->user["remote_last_results"]["R_getRadarFleets"]["data"]["fleet"] as $fleet){
				if ($fleet["time"]<$min_attack_time){
					$min_attack_time=$fleet["time"];
					$attaked_planet=$fleet["target"][0]."_".$fleet["target"][1]."_".$fleet["target"][2];
					$pirates=$fleet["target"][0]."_".$fleet["target"][1]."_17";
				}
			}
			echo "attack in ".$min_attack_time."\n";
			//some attack less than 30 sec, save SD if its possible
			if ($min_attack_time<$save_when){
				$gaw->G_updatePlanetsInfo(array($attaked_planet),0);
				if ($gaw->user["planets"][$attaked_planet]["info"]["data"]["spacecraft"][9]["count"]>0){
					$save_ships[9]=$gaw->user["planets"][$attaked_planet]["info"]["data"]["spacecraft"][9]["count"];
					$gas=intval($gaw->user["planets"][$attaked_planet]["info"]["data"]["res"][2]["now"]-1000);
					if ($gas<0)$gas=0;
					$res=array("0"=>0,"1"=>0,"2"=>$gas);
					$planets=array(
						"to"=>$pirates,
						"from"=>$attaked_planet
					);
					$gaw->R_sentFleet(8,$planets,$res,$save_ships);
					$save_uid[$attaked_planet]=$gaw->user["remote_last_results"]["R_sentFleet"]["data"]["fleet_uid"];
					echo Date("c")." save fleet on $attaked_planet due attack less than 30 sec (uid: ".$save_uid[$attaked_planet].")\n";
				}
			}
		}
		else{
			foreach($save_uid as $planet=>$fleet_uid){
				echo Date("c")." come back fleet on planet $planet (uid: $fleet_uid)\n";
				$gaw->R_cancelFleet($fleet_uid);
				unset($save_uid[$planet]);
			}
		}
		$last_attack_check_time=0;
	}
	// ***** SAVE PROCEDURE
	$gaw->R_getInviteUnionFleets();
	//get presistent ships
	if (count($gaw->user["remote_last_results"]["R_getInviteUnionFleets"]["data"]["fleet"])>0){
		$gaw->G_updatePlanetsInfo($planets,0);
		$ships=array();
		foreach($planets as $planet){
			$ships[$planet]=$gaw->user["planets"][$planet]["info"]["data"]["spacecraft"][9]["count"];
			if (!is_numeric($ships[$planet]))
				$ships[$planet]=0;
		}
		// look requests
		foreach ($gaw->user["remote_last_results"]["R_getInviteUnionFleets"]["data"]["fleet"] as $fleet){
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
				$gaw->R_agreeUnionFleet($save_from,$fleet["fleet_uid"]);
				$gaw->R_cancelFleet($fleet["fleet_uid"]);
				$ships[$save_from]=$ships[$save_from]-1;
			}
		}
	}
	// **** ACCEPT NEW FRIEND
	$gaw->R_getRequestList();
	if (count($gaw->user["remote_last_results"]["R_getRequestList"]["data"]["request"])>0){
		foreach($gaw->user["remote_last_results"]["R_getRequestList"]["data"]["request"] as $request){
			$gaw->R_acceptFriend($request["user_id"]);
		}
	}
	// **** CHECK FRIENDS ONLINE every 30 sec and update statuses on wiki
	// need update, need update status here (read from wiki old), and update wiki only in change
	if ($last_check_friends>=30){
		//check online and update wiki
		$gaw->R_getFrientList();
		foreach($gaw->user["remote_last_results"]["R_getFrientList"]["data"]["friends"] as $friend){
			if ($friend["heart_beat"]<40) {$status="online";$dbon="true";}
			else {$status="offline";$dbon="false";}
			pg_query($gaw->db,"insert into bot0 (user_name,online,offline_seconds,last_update) values ('".$friend["user_name"]."',".$dbon.",".$friend["heart_beat"].",CURRENT_TIMESTAMP) on conflict (user_name) do update set online=".$dbon.",offline_seconds=".$friend["heart_beat"].",last_update=CURRENT_TIMESTAMP;");
			//exec("./set_status.sh '".$friend["user_name"]."' $status");
		}
		$last_check_friends=0;
	echo "status updated\n";
	}
	// **** WAITING PERIOD 10 sec
	$gaw->G_Sleep(10);
	$last_attack_check_time+=10;
	$last_check_friends+=10;
}
?>

