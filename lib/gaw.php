<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '.');
include('gaw_raw.php');


class GAW extends GAW_RAW{
	public $DEBUG=4;// 1 - ERROR, 2 - WARNING, 3 - INFO, 4 - DEBUG1, 5 - DEBUG2, 6 - DEBUG3
	public $EXIT_ON_ERROR=false;
	//***** PUBLIC FUNCTIONS -  API FOR OTHERS ******
	public function G_login (){
		//$this->_get_login_password(); ## moved to constructor
		if (($this->user['game_data']["password_hash"]=="")or($this->user['game_data']["password_hash"]=="null")){
			//first login
			$this->R_login();
			pg_query($this->db,"update accounts set account_id='".$this->user['game_data']['account_id']."',password_hash='".$this->user['game_data']['password_hash']."' where acccount='".$this->user['acccount']."';");
		}
		if (!$this->R_auto_login()){
			//wrong password
			$this->R_login();
			pg_query($this->db,"update accounts set account_id='".$this->user['game_data']['account_id']."',password_hash='".$this->user['game_data']['password_hash']."' where acccount='".$this->user['acccount']."';");
			if (!$this->R_auto_login())
				die ("Cannot login, maybe wrong password\n");
		}
		$this->R_getUserList();
		foreach ($this->user['remote_last_results']['R_getUserList']['data']['users'] as $u)
			pg_query($this->db,"insert into users (user_id,user_name,level,score,acccount) values (".$u['user_id'].",'".$u['user_name']."',".$u['commander_info']['level'].",".$u['personal_score'].",'".$this->user['acccount']."') on conflict (user_name) do update set level=".$u['commander_info']['level'].",score=".$u['personal_score'].",acccount='".$this->user['acccount']."',user_id=".$u['user_id'].";");
		if ($this->user["user_name"]!=""){
			$this->R_enterGame();
			$this->R_getUnitConfig();
			$this->R_getItemPrice();
			$this->R_getUserPlanetList();
			$this->user["mother"]=
				$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][0]."_".
				$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][1]."_".
				$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][2];
			$this->user['planets']=array();
			foreach ($this->user['remote_last_results']['R_getUserPlanetList']['data']['planets'] as $val){
				$this->user['planets'][$val['position'][0]."_".$val['position'][1]."_".$val['position'][2]]=$val;
			}
			$this->_sync_planets_to_db();
			$this->G_ping(true);
		}
	}
	public function G_ping($now=false){
		$doping=false;
		if (strtotime("now")-strtotime($this->user["remote_last_results"]["R_tick"]['update'])>30)
			$doping=true;
		if ($now)
			$doping=true;
		if ($doping==true){
			$this->R_tick();
			$this->R_getGameDataEx();
			pg_query("update users set gold=".$this->user['remote_last_results']['R_getGameDataEx']['data']['gold']." where user_name='".$this->user['user_name']."';");
			if ($this->user['remote_last_results']['R_getGameDataEx']['data']['gift_state']==1)
				$this->G_gatherPresents();
		}
	}
	public function G_updatePlanetsInfo($what_check,$delay){
		//last must take data from mother position
		$planets=array();
		if ((is_string($what_check))and($what_check=="all")){
			foreach ($this->user['remote_last_results']['R_getUserPlanetList']['data']['planets'] as $val){
				$planets[]=$val['position'][0]."_".$val['position'][1]."_".$val['position'][2];
			}
		}else{
			$planets=$what_check;
		}
		//need check if some active user is owner of some planet_id
		foreach ($planets as $planet_loc){
			$do=false;
			if (isset($this->user["planets"][$planet_loc]["info"]['update'])){
				if (strtotime("now")-strtotime($this->user["planets"][$planet_loc]["info"]['update'])>=$delay)
					$do=true;
			}
			else 
				$do=true;
			if ($do==true){
				$this->user["planets"][$planet_loc]["info"]["update"]=Date("c");
				$this->R_getPlanetInfo($planet_loc);
				$this->user["planets"][$planet_loc]["info"]["data"]=$this->user['remote_last_results']['R_getPlanetInfo']['data'];
			}
		}
		$this->_sync_planets_to_db();
	}
	public function G_sleep($total_sleep){
		$sleep_sec=1;
		$tek_sleep=0;
		$step_sec=0;
		while (true){
			$this->G_ping();
			$tek_sleep=$tek_sleep+$sleep_sec;
			$step_sec=$step_sec+$sleep_sec;
			if ($step_sec>60){
				$left=$total_sleep-$tek_sleep;
				echo "left ".$left."\n";
				$step_sec=0;
			}
			if ($tek_sleep>$total_sleep)
				break;
			sleep ($sleep_sec);
		}
	}
	public function G_Spacecraft($what_check){
		//last must take data from mother position
		$planets=array();
		if ((is_string($what_check))and($what_check=="all")){
			foreach ($this->user['remote_last_results']['R_getUserPlanetList']['data']['planets'] as $val){
				$planets[]=$val['position'][0]."_".$val['position'][1]."_".$val['position'][2];
			}
		}
		//need check if some active user is owner of some planet_id
		foreach ($planets as $planet_loc){
			$this->user["planets"][$planet_loc]["spacecraft"]["update"]=Date("c");
			$this->R_getSpacecraft($planet_loc);
			$this->user["planets"][$planet_loc]["spacecraft"]["data"]=$this->user['remote_last_results']['R_getSpacecraft']['data'];
		}
		$this->_sync_planets_to_db();
	}
	public function G_Save($with_res=true){
		//send fleet to save, friends online first
		//расчитать сколько ресов отправить в сейв с учётом 100% заполненности, сначала газ, потом крисы, потом метал
		$this->R_getFrientList();
		$mother=
			$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][0]."_".
			$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][1]."_".
			$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][2];
		$pirates=
			$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][0]."_".
			$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][1]."_17";
		$this->G_updatePlanetsInfo(array($mother),0);
		$this->R_getSpacecraft($mother);
		$ships=array(
			"22"=>$this->user["planets"][$mother]["spacecraft"]["data"][22],
			"23"=>$this->user["planets"][$mother]["spacecraft"]["data"][23],
			"3"=>$this->user["planets"][$mother]["spacecraft"]["data"][3],
			"1"=>$this->user["planets"][$mother]["spacecraft"]["data"][1]
		);
		$leave_gaz=((intval($ships['22']/100)+1)*2500)+((intval($ships['23']/100)+1)*2000)+((intval($ships['3']/100)+1)*2000)+((intval($ships['1']/100)+1)*1500)+100000;
		$res_max=intval((($ships[1]*25000)+($ships[22]*75000)+($ships[23]*40000)));
		$kk_max=intval((($ships[1]*25000)+($ships[22]*75000)+($ships[23]*40000))/1000000);
		$this->user['last_fleet_save']['res_max']=$kk_max*1000000;
		$planets["to"]=$pirates;
		$planets["from"]=$mother;
		$gas=$this->user["planets"][$mother]["info"]["data"]["res"][2]["now"]-$leave_gaz;
		if ($gas<0)$gas=0;
		if ($with_res==true){
				$res=array(
					"0"=>intval($this->user["planets"][$mother]["info"]["data"]["res"][0]["now"]),
					"1"=>intval($this->user["planets"][$mother]["info"]["data"]["res"][1]["now"]),
					"2"=>intval($gas)
				);
		}else{
			if ($gas>=$leave_gaz)
				$gas=$leave_gaz;
			$res=array("0"=>0,"1"=>0,"2"=>intval($gas));
		}
                for ($rr=0;$rr<=2;$rr++){
                        if (($res[0]+$res[1]+$res[2])>$res_max){
                                $res[$rr]=$res[$rr]-(($res[0]+$res[1]+$res[2])-$res_max);
                                if ($res[$rr]<0)$res[$rr]=0;
                                else break;
                        }
                }
		$kk_res=intval(($res[0]+$res[1]+$res[2])/1000000);
		$kk_p=intval($kk_res*100/$kk_max);
		$this->R_sentFleet(8,$planets,$res,$ships);
		$this->user['last_fleet_save']['res'][0]=$res[0];
		$this->user['last_fleet_save']['res'][1]=$res[1];
		$this->user['last_fleet_save']['res'][2]=$res[2];
		$this->user['last_fleet_save']['total_res']=$kk_res;
		$this->user['last_fleet_save']['total_percent']=$kk_p;
		$save_uid=$this->user['remote_last_results']["R_sentFleet"]['data']['fleet_uid'];
		$this->user['last_fleet_save']['fleet_uid']=$save_uid;
		$save_done=false;
		foreach ($this->user['savers_users'] as $save_user){
			$save_user_id=$this->_is_user_online($save_user);
			if ($save_user_id>0){
				$this->R_applyUnion($save_uid,$save_user_id);
				$this->G_sleep(10);
        			$this->R_getAllInfo ();
				foreach($this->user['remote_last_results']['R_getAllInfo']['data']['fleet'] as $fleet){
					if (($fleet['fleet_uid']==$save_uid)and($fleet['time']>200000)){
						$save_done=true;
						$this->user['last_fleet_save']['fleet_uid']=$fleet['fleet_uid'];
						$this->user['last_fleet_save']['time']=$fleet['time'];
						break;
					}
				}
			}
			if ($save_done!=false)
				break;
		}
		return $save_done;
	}
	public function G_gatherPresents(){
		//check if first request then update
                $this->R_getActivityList();
                if (isset($this->user['remote_last_results']['R_getActivityList']['data']['activity'])){
                        foreach ($this->user['remote_last_results']['R_getActivityList']['data']['activity'] as $val){
                                if ($val['state']==1){
                                        echo "take present number ".$val['text']['title']."\n";
                                        $this->R_getActivityReward($val['activity_id']);
                                }
                        }
                }
	}
	public function G_Exit(){
		echo "";
	}
}
?>

