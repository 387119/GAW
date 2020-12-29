<?php
set_include_path(get_include_path() . PATH_SEPARATOR . '.');
include('gaw_raw.php');


class GAW extends GAW_RAW{
	public $DEBUG=4;// 1 - ERROR, 2 - WARNING, 3 - INFO, 4 - DEBUG1, 5 - DEBUG2, 6 - DEBUG3
	public $EXIT_ON_ERROR=false;
	public $db;
	public $cfg=array(
		"user_id"=>"",
		"user_name"=>"",
		"server_id"=>"101"
	);
        public function __construct(){
		//initialization
		parent::__construct();
                $this->db=pg_connect("host=localhost port=5432 dbname=gaw user=gaw password=gaw") or die('connection to db failed');
	}
	//****** INTERNAL FUNCTION ********
	private function _pre_get_account(){
		if ($this->cfg['user_id']!="")
			$sql="select account_id,acccount,passwd,password_hash from accounts where account_id in (select account_id from accounts_users where user_id=".$this->cfg['user_id'].");";
		elseif (($this->cfg['user_name']!="")and($this->cfg['server_id']!=""))
			$sql="select account_id,acccount,passwd,password_hash from accounts where account_id in (select account_id from accounts_users where user_id in (select user_id from users where user_name='".$this->cfg['user_name']."' and server_id=".$this->cfg['server_id']."));";
		else return false;
		$res=pg_query($this->db,$sql);
		$resf=pg_fetch_all($res);
		if (isset($resf[0])){
			$this->cfg['account_id']=$resf[0]['account_id'];
			$this->cfg['acccount']=$resf[0]['acccount'];
			$this->cfg['psw_clear']=$resf[0]['passwd'];
			$this->cfg['password_hash']=$resf[0]['password_hash'];
		}
	}
        private function _pre_get_device(){
		if ($this->cfg['user_id']!="")
			$filter="user_id='".$this->cfg['user_id']."'";
		elseif (($this->cfg['user_name']!="")and($this->cfg['server_id']!=""))
			$filter="user_name='".$this->cfg['user_name']."' and server_id=".$this->cfg['server_id']."";
		else return false;
		$res=pg_query($this->db,"select device_id from users where $filter;");
		$resf=pg_fetch_array($res,NULL);
		$this->cfg['device_id']=$resf['device_id'];
	}
	private function _db_update_device(){
		if (($this->user['user_name']!="")and($this->user['game_data']['device_id']!=""))
			pg_query($this->db,"update users set device_id='".$this->user['game_data']['device_id']."' where user_name='".$this->user['user_name']."' and device_id!='".$this->user['game_data']['device_id']."';");
	}
	private function _db_update_user(){
		if (isset($this->user['remote']['nmUser/getGameDataEx']['response']['data'])){
			if (!isset($this->user['remote']['nmUser/getGameDataEx']['response']['data']['gold']))
				$gold=0;
			else
				$gold=$this->user['remote']['nmUser/getGameDataEx']['response']['data']['gold'];
			pg_query("update users set gold=$gold where user_name='".$this->user['game_data']['user_id']."';");
		}
	}
	private function _get_server_id(){
		if ($this->cfg['user_id']!=""){
			$res=pg_query($this->db,"select server_id from users where user_id=".$this->cfg['user_id'].";");
			$resf=pg_fetch_array($res,NULL);
			$this->cfg['server_id']=$resf['server_id'];
		}
	}
	public function _is_online(){
		if ($this->user['game_data']['user_id']=='')
			return false;
		$res=pg_query($this->db,"select online from bot0 where user_id=".$this->user['game_data']['user_id'].";");
		$resf=pg_fetch_array($res,NULL);
		$online=$resf['online'];
		if ($online == 't')
			$ret=true;
		else
			$ret=false;
		return $ret;
	}
	public function _get_savers(){
                $res=pg_query($this->db,'select user_id,user_name from users where type=5 and enabled=true and server_id='.$this->user['game_data']['server_id'].';');
                $this->user['savers']=pg_fetch_all($res);
        }
        public function _get_login_password(){
                //get login name by Commander name
                $res=pg_query($this->db,"select passwd,account_id,password_hash from accounts where acccount='".$this->user['acccount']."';");
                $resf=pg_fetch_array($res,NULL);
                if (count($resf)>0){
                        $this->user['game_data']['psw_clear']=$resf['passwd'];
                        $this->user['game_data']['password_hash']=$resf['password_hash'];
                        $this->user['game_data']['account_id']=$resf['account_id'];
                        $this->_setup_password();
                }else{
                        $this->user['game_data']['psw_clear']="";
                        $this->user['game_data']['password_hash']="";
                        $this->user['game_data']['account_id']="";
                        $this->user['game_data']['psw']="";
                        $this->user['game_data']['account_key']="";
                }
        }
        public function _sync_planets_to_db (){
                //update info about planets in db
                foreach($this->user['planets'] as $pos => $pl){
			if (isset($pl['list']['data'])){
				$d=$pl['list']['data'];
	                        $size=json_encode(array("now"=>$d['size']['now'],"max"=>$d['size']['max']));
	                        if ($pos==$this->user['mother']) $m="true";
	                        else $m="false";
	                        pg_query("insert into planets 
	                                        (position,planet_name,mother,user_id,temp,size,skin_id) 
	                                values ('".$pos."','".$d['name']."',$m,".$this->user['game_data']['user_id'].",".$d['temperature'].",'$size',".$d['skin_id'].")
	                                on conflict (position) 
	                                do update set last_list_update=now(),planet_name='".$d['name']."',mother=$m,user_id=".$this->user['game_data']['user_id'].",temp=".$d['temperature'].",size='$size',skin_id=".$d['skin_id'].";");
                        }
			if (isset($pl['info']['data'])){
                                $i=$pl['info']['data'];
       	                        $res=json_encode(array(intval($i['res'][0]['now']),intval($i['res'][1]['now']),intval($i['res'][2]['now'])),JSON_FORCE_OBJECT);
               	                $power=json_encode(array("now"=>$i['power']['now'],"max"=>$i['power']['max']));
                       	        $abuild=array();
                               	foreach($i['build'] as $key=>$val){
                                        $abuild[$key]=$val['lv'];
       	                        }
               	                $build=json_encode($abuild,JSON_FORCE_OBJECT);
                       	        $aspacecraft=array();
                               	foreach($i['spacecraft'] as $key=>$val){
                                        $aspacecraft[$key]=$val['count'];
       	                        }
               	                $spacecraft=json_encode($aspacecraft,JSON_FORCE_OBJECT);
                       	        pg_query("update planets set last_detail_update=now(),res='$res',power='$power',build='$build',spacecraft='$spacecraft' where position='$pos';");
			}
                }
        }
	//***** PUBLIC FUNCTIONS -  API FOR OTHERS ******
	public function G_InitAcc($acccount,$server_id){
		//init without user_name, need for first login and create user_list
		$this->cfg['server_id']=$server_id;
		$res=pg_query($this->db,"select account_id,acccount,passwd,password_hash from accounts where acccount='$acccount';");
		$resf=pg_fetch_all($res);
		if (isset($resf[0])){
			$this->cfg['account_id']=$resf[0]['account_id'];
			$this->cfg['acccount']=$resf[0]['acccount'];
			$this->cfg['psw_clear']=$resf[0]['passwd'];
			$this->cfg['password_hash']=$resf[0]['password_hash'];
		}
		$this->R_Init($this->cfg);
	}
	public function G_InitId ($user_id){
		// init with user_id
		$this->cfg['user_id']=$user_id;
		$this->_get_server_id();
		$this->_pre_get_account();
		$this->_pre_get_device();
		$this->R_Init($this->cfg);
		//set device_id for user (if new)
		$this->_get_savers();
	}
        public function G_InitName($user_name,$server_id){
		$this->cfg['user_name']=$user_name;
		if ($server_id!="")
			$this->cfg['server_id']=$server_id;
		$this->_pre_get_account();
		$this->_pre_get_device();
		$this->R_Init($this->cfg);
		//set device_id for user (if new)
		$this->_get_savers();
        }
	public function G_serverList(){
		//list servers and update DB
		$data["pd"]=array("app_key"=>$this->user['game_data']['app_key'],"spx_did"=>$this->user['game_data']['spx_did'],"publish"=>"google","device"=>array("gp_adid"=>$this->user['game_data']['device_id'],"android_id"=>"-1","ios_idfa"=>"-1","mac_address"=>"-1","platform"=>"android"),"info"=>array("app_version"=>$this->user['game_data']['pkg_version'],"os_version"=>$this->user['game_data']['device_os_version'],"content_version"=>$this->user['game_data']["sdk_ver"],"platform"=>"android","device_type"=>$this->user["game_data"]["device_detail_type"]));
		$this->R_Remote("/spx_gsm/api_game_server/serverList/ING004/google/0201/0/ru");
		foreach ($this->user["remote"]["/spx_gsm/api_game_server/serverList/ING004/google/0201/0/ru"]["response"]["data"]["server"] as $srv){
			extract($srv,EXTR_OVERWRITE);
			pg_query("insert into servers (server_id,name,country,state,new,address) values ($server_id,'$name','$country',$state,$new,'$address') on conflict (server_id) do update set state=$state;");
		}
	}       
	public function G_Login (){
		//login to commander
		if ($this->_is_online()==true)
			die(Date("c")." user is online, try again later \n");
		if (($this->user['game_data']["password_hash"]=="")or($this->user['game_data']["password_hash"]=="null")){
			//first login
			$this->R_Remote('api_account/login');
			pg_query($this->db,"update accounts set account_id='".$this->user['game_data']['account_id']."',password_hash='".$this->user['game_data']['password_hash']."' where acccount='".$this->user['acccount']."';");
		}
		$this->R_Remote('api_account/auto_login');
		if ($this->user['remote']['api_account/auto_login']['response']['data']['error_code']!=1){
			//wrong password
			$this->R_Remote('api_account/login');
			pg_query($this->db,"update accounts set account_id='".$this->user['game_data']['account_id']."',password_hash='".$this->user['game_data']['password_hash']."' where acccount='".$this->user['acccount']."';");
			$this->R_Remote('api_account/auto_login');
			if ($this->user['remote']['api_account/auto_login']['response']['data']['error_code']!=1)
				die ("Cannot login, maybe wrong password\n");
		}
		$this->R_Remote('nmLogin/getUserList');
		foreach ($this->user['remote']['nmLogin/getUserList']['response']['data']['users'] as $u){
			pg_query($this->db,"insert into users (user_id,server_id,user_name,level,score,last_update) values (".$u['user_id'].",".$this->user['game_data']['server_id'].",'".$u['user_name']."',".$u['commander_info']['level'].",".$u['personal_score'].",CURRENT_TIMESTAMP) on conflict (user_id) do update set user_name='".$u["user_name"]."',level=".$u['commander_info']['level'].",score=".$u['personal_score'].",last_update=CURRENT_TIMESTAMP;");
			pg_query($this->db,"insert into accounts_users (account_id,user_id) values (".$this->user['game_data']['account_id'].",".$u['user_id'].") on conflict do nothing;");
		}
		if ($this->user["user_name"]!=""){
			pg_query ($this->db,"update users set device_id='".$this->user['game_data']['device_id']."' where user_id=".$this->user['game_data']['user_id'].";");
			$this->R_Remote('nmLogin/enterGame');
			$this->R_Remote('nmUnit/getUnitConfig');
			$this->R_Remote('nmItem/getItemPrice');
			$this->R_Remote('nmUser/getUserPlanetList');
			$this->_sync_planets_to_db();
			$this->G_Ping();
		}
	}
	public function G_Ping(){
		//ping
		$this->R_Ping();
		$this->_db_update_user();
	}
	public function G_updatePlanets($what_check=false,$level=false){
		//level 1-list,2-info,3-list+info,4-spacecraft,5-info+spacecraft,6-list+info+spacecraft
		//last must take data from mother position
		$delay=0;
		$planets=array();
		if (!isset($level))
			$level=1;
		switch ($level){
			case "1":
			case "3":
			case "6":
				$this->R_Remote('nmUser/getUserPlanetList');
				break;
		}
		if ($what_check==false){
			foreach ($this->user['planets'] as $k=>$val){
				$planets[]=$k;
				//$planets[]=$val['position'][0]."_".$val['position'][1]."_".$val['position'][2];
			}
		}else{
			if (is_string($what_check))
				$planets[]=$what_check;
			else
				$planets=$what_check;
		}
		//need check if some active user is owner of some planet_id
		foreach ($planets as $planet_loc){
			$do=false;
			if (isset($this->user["planets"][$planet_loc]["info"]['update'])){
				if (strtotime("now")-strtotime($this->user["planets"][$planet_loc]["info"]['update'])>=$delay)////////
					$do=true;
			}
			else 
				$do=true;
			if ($do==true){
				switch ($level){
					case "2":
					case "3":
					case "5":
					case "6":
						$this->R_Remote('nmPlanet/getPlanetInfo',array("ex_data"=>array("planet_id"=>$planet_loc)));
						break;
				}
				switch ($level){
					case "4":
					case "5":
					case "6":
						$this->R_Remote('nmUnit/getSpacecraft',array("ex_data"=>array("planet_id"=>$planet_loc)));
						break;
				}
			}
		}
		$this->_sync_planets_to_db();
	}
	public function G_Sleep($total_sleep){
		//sleep n seconds
		$sleep_sec=1;
		$tek_sleep=0;
		$step_sec=0;
		while (true){
			$this->G_Ping();
			$tek_sleep=$tek_sleep+$sleep_sec;                                                                                                                                     
			$step_sec=$step_sec+$sleep_sec;                                                                                                                                       
			if ($step_sec>60){
				$left=$total_sleep-$tek_sleep;                                                                                                                                
				echo Date("c")." sleep $total_sleep sec, left ".$left." sec\n";                                                                                               
				$step_sec=0;                                                                                                                                                  
			}
			if ($tek_sleep>$total_sleep)
				break;
			sleep ($sleep_sec);                                                                                                                                                   
		}
	}
	public function G_Save($iplanet,$iship,$ires,$ispeed){
		/*
			iship - тип сейва кораблей
				*1 - Всего что есть (общий, по умолчанию)
				2 - Только грузовой сейв (автосборщиков)
				3 - Только звёзды (для сейвера)
				array - явное указание списка кораблей
			ires - Тип что делать с ресами при отправке сейва
				*1 - Все что сможет влезть (общий, по умолчанию)
				2 - Ничего не сейвить
				3 - Только газ необходимый для последующего пересейва
				array - явное указание списка ресов
			ispeed - скорость на которой отправить сейв
				1 - Выбор минимальной скорости для которой хватит газа для отправки. (не реализованно)
				>=10 - явное указание скорости без ускорения
				>=1010 - явное указание скорости с ускорением
				1100 - по умолчанию если не указанно другого
			iplanet - Планета отправки
				string - явное указание планеты
				* 1 - материнская планета (по умолчанию)
		*/
		//Если сейверы не определены то и сейвить нет смысла
		if (count($this->user['savers'])==0){
			echo Date("c")." WARNING: cannot send save, no Savers found\n";
			return false;
		}
		//Определение планет откуда и куда отправлять сейв
		$ex_data=array();
		switch($iplanet){
			case "1":
				$ex_data['start_pos']=$this->user['mother'];
				break;
			default:
				$tmp=explode($iplanet);
				if ((is_numeric($tmp[0]))and (is_numeric($tmp[1]))and(is_numeric($tmp[2])))
					$ex_data['start_pos']=$iplanet;
				else
					$ex_data['start_pos']=$this->user['mother'];
				break;
		}
		$tmp=explode("_",$ex_data['start_pos']);
		$ex_data['end_pos']=$tmp[0]."_".$tmp[1]."_17";
		//Расчёт кораблей которые надо отправить в сейв
		if (is_array($iship))
			$ex_data['bring_ship']=$iship;
		else{
			$this->G_updatePlanets($ex_data['start_pos']);
			switch ($iship){
				case "2":
					$ex_data['bring_ship']=array(
						"22"=>$this->user["planets"][$ex_data['start_pos']]["spacecraft"]["data"]["data"][22],
						"23"=>$this->user["planets"][$ex_data['start_pos']]["spacecraft"]["data"]["data"][23],
						"3"=>$this->user["planets"][$ex_data['start_pos']]["spacecraft"]["data"]["data"][3],
						"1"=>$this->user["planets"][$ex_data['start_pos']]["spacecraft"]["data"]["data"][1]
					);
					break;
				case "3":
					$ex_data['bring_ship']=array(
						"9"=>$this->user["planets"][$ex_data['start_pos']]["spacecraft"]["data"]["data"][9]
					);
					break;
				default:
					foreach ($this->user["planets"][$ex_data['start_pos']]["spacecraft"]["data"]["data"] as $k=>$v)
						$ex_data['bring_ship'][$k]=$v;
					break;
			}
		}
		//Расчёт параметров скорости отправки
		if ($ispeed>=1000){
			$ex_data['upshift']=1;
			$ex_data['rate']=intval($ispeed-1000);
			if ($ex_data['rate']<10)$ex_data['rate']=10;
			if ($ex_data['rate']>100)$ex_data['rate']=100;
		}elseif($ispeed>=10){
			$ex_data['upshift']=0;
			$ex_data['rate']=$ispeed;
			if ($ex_data['rate']>100)$ex_data['rate']=100;
		}else{
			//значение < 10 зарезервированны для интелектуальных выставлений скорости при сейве, пока не реализованно
			$ex_data['upshift']=1;
			$ex_data['rate']=100;
		}
		//Расчёт необходимого газа (реализованно неправильно, пока беру максимум)
		$ngaz=0;
		foreach ($ex_data['bring_ship'] as $k => $v){
			// для 100 шт 22 типа надо 2500, 23-2000, 3-2000, 1-1500
			$ngaz+=(intval($v/100)+1)*4000;
		}
		//Расчёт ресурсов которые будут уходить в сейв с учётом газа необходимого для отправки
		echo Date("c")." DETAIL: gaz on main:".intval($this->user["planets"][$ex_data['start_pos']]["info"]["data"]["res"][2]["now"])."\n";
		if (is_array($ires)){
			$ex_data['bring_res']=$ires;
		}else{
			switch($ires){
				case "2":
					$ex_data['bring_res']=array("0"=>0,"1"=>0,"2"=>0);
					break;
				case "3":
					if ($this->user["planets"][$ex_data['start_pos']]["info"]["data"]["res"][2]["now"]>$ngaz)
						$sgaz=$ngaz;
					else
						$sgaz=$this->user["planets"][$ex_data['start_pos']]["info"]["data"]["res"][2]["now"];
					$ex_data['bring_res']=array("0"=>0,"1"=>0,"2"=>intval($sgaz));
					break;
				default:
					$ex_data['bring_res']=array(
						"0"=>intval($this->user["planets"][$ex_data['start_pos']]["info"]["data"]["res"][0]["now"]),
						"1"=>intval($this->user["planets"][$ex_data['start_pos']]["info"]["data"]["res"][1]["now"]),
						"2"=>intval($this->user["planets"][$ex_data['start_pos']]["info"]["data"]["res"][2]["now"])
					);
					$rmax=0;
					foreach ($ex_data['bring_ship'] as $k=>$v){
						switch ($k){
							case "1":
								$rmax+=$v*25000;
								break;
							case "22":
								$rmax+=$v*75000;
								break;
							case "23":
								$rmax+=$v*40000;
								break;
							case "9":
								$rmax+=$v*10000000;
								break;
						}
					}
					if ($ex_data['bring_res'][0]+$ex_data['bring_res'][1]+$ex_data['bring_res'][2]>$rmax){
						//ресов больше чем можно увезти, убираем в порядке Металл, кристал, газ
						for ($rr=0;$rr<=2;$rr++){
							if (($ex_data['bring_res'][0]+$ex_data['bring_res'][1]+$ex_data['bring_res'][2])>$rmax){
								$ex_data['bring_res'][$rr]=intval($ex_data['bring_res'][$rr]-(($ex_data['bring_res'][0]+$ex_data['bring_res'][1]+$ex_data['bring_res'][2])-$rmax));
								if ($ex_data['bring_res'][$rr]<0)$ex_data['bring_res'][$rr]=0;
								else break;
							}
						}
					}
					break;
			}
		}
		// Вычисляем и оставляем лишний газ на планке
		$gaz_left=$this->user["planets"][$ex_data['start_pos']]["info"]["data"]["res"][2]["now"]-$ex_data['bring_res'][2];
		if ($gaz_left<$ngaz){
			$need_more_gaz=$ngaz-$gaz_left;
			$ex_data['bring_res'][2]-=$need_more_gaz;
			if ($ex_data['bring_res'][2]<0){
				echo Date("c")."WARNING: Save stoped!! no gaz for save on planet ".$ex_data['start_pos'].", need $ngaz , present ".intval($this->user["planets"][$ex_data['start_pos']]["info"]["data"]["res"][2]["now"])."\n";
				break;
			}
		}
		// Устанавливаем тип отправки
		$ex_data['purpose']=8;
		// Отправляем сейв
		$this->R_Remote('nmFleet/sentFleet',array('ex_data'=>$ex_data));
		// Кидаем приглос для продления
		// ***** Изменить этот блок так чтоб он поддерживал работу с несколькими сейверами
		//$save_done=false;
		$save_uid=$this->user['remote']["nmFleet/sentFleet"]['response']['data']['fleet_uid'];
		foreach ($this->user['savers'] as $saver){
			//$saver_id=$this->_is_saver_online($saver[]);
			//if ($saver_id>0){
				$ex_apply=array("fleet_uid"=>$save_uid,"target_user_id_array"=>array("0"=>$saver['user_id']));
				$this->R_Remote('nmFleet/applyUnion',array('ex_data'=>$ex_apply));
				//$this->G_sleep(10);
        			//$this->R_getAllInfo ();
				//foreach($this->user['remote_last_results']['R_getAllInfo']['data']['fleet'] as $fleet){
				//	if (($fleet['fleet_uid']==$save_uid)and($fleet['time']>200000)){
				//		$save_done=true;
				//		$this->user['last_fleet_save']['fleet_uid']=$fleet['fleet_uid'];
				//		$this->user['last_fleet_save']['time']=$fleet['time'];
				//		break;
				//	}
				//}
			//}
			//if ($save_done!=false)
			//	break;
		}
		$iships=$ex_data['bring_ship'];
		if (!isset($iships[1]))$iships[1]=0;
		if (!isset($iships[22]))$iships[22]=0;
		if (!isset($iships[23]))$iships[23]=0;
		$kk_max=intval((($iships[1]*25000)+($iships[22]*75000)+($iships[23]*40000))/1000000);
                $kk_res=intval(($ires[0]+$ires[1]+$ires[2])/1000000);
                $kk_p=intval($kk_res*100/$kk_max);
                $this->user['last_fleet_save']['res']=$ex_data['bring_res'];
                $this->user['last_fleet_save']['res_max']=$kk_max;
                $this->user['last_fleet_save']['total_res']=$kk_res;
                $this->user['last_fleet_save']['total_percent']=$kk_p;
                $this->user['last_fleet_save']['fleet_uid']=$save_uid;
		//return $save_done;
	}
	public function G_Exit(){
		// I dont know if this function need
		echo Date("c")." Exit";
		die();
	}
}
?>

