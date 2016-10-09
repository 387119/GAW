<?php
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
include('Crypt/RSA.php');


class GAW {
	private $hosts=array(
		"spx"=>'54.183.10.158',                 // "/spx_*"
		"ing"=>'ing0042.sphinxjoy.net'         // "/ING*"
	);
	private $RSA_KEY="-----BEGIN RSA PUBLIC KEY-----\nMIGJAoGBAKv4OKlpY2oq9QZPMzAjbQfiqDqTnisSvdLP+mTswZJdbtk1J+4+qAyS\nJuZjSQljzcUu0ANg+QG0VsvoU72zu5pErZKWubfe9HB/tq69bhP60qgP6/W2VebW\nlqUNGtsMedxuVaFBL3SoqU7e5RELIsuArCJJIgz86BQDX0x63VpXAgMBAAE=\n-----END RSA PUBLIC KEY-----";
	private $crypt;
	public $user=array();
	private $root_url="/ING004/n/WebServer/Web/sogame/newControl";
	public $urls=array(
		// + not standart requests 
		// minimum need for login
		"R_getItemPrice"=>"nmItem/getItemPrice",
		"R_getUnitConfig"=>"nmUnit/getUnitConfig",
		"R_getUserData"=>"nmUser/getUserData",//global info about user
		"R_getUserPlanetList"=>"nmUser/getUserPlanetList",// list of user planets
		// next work process
		"R_getUniverse"=>"nmUniverse/getUniverse",//list of planets in system
		"R_getGameDataEx"=>"nmUser/getGameDataEx",//get online info
		"R_tick"=>"nmUser/tick", // ping every 30 sec
		"R_getAllInfo"=>"nmFleet/getAllInfo",// 43 list of fleets in the fly
		"R_cancelFleet"=>"nmFleet/cancelFleet",// 44 push back to fleet
		"R_sentFleet"=>"nmFleet/sentFleet", // sent fleet to save or attack (but not move to planet)
		"R_getPlanetData"=>"nmPlanet/getPlanetData",// - 38 show info abount planet res (need check can we do it without getPlanetInfo)
		"nmActivity/getActivityList",
		"nmBuild/refresh",
		"nmFleet/getRadarFleets", // get fleets in radar
		"nmGiftbag/reflushRecommend", 
		"nmMerchant/getMerchant",
		"nmPlanet/getPlanetInfo",// - 33 switch to planet
		"nmTimeActivity/getGiftBoxCount", // I this it`s presents button
		"nmUnit/getSpacecraft",// open Spacecraft (Kosmoverf)
		"nmUnit/product", // make new ships 
		// maybe need
		"nmActive/getActiveDescInfo",
		"nmActivity/getEvent",
		"nmAllianceTec/getTecsInfo",
		"nmControl/getBlueprintsConfig",
		"nmControl/openAllianceControl",
		"nmFleet/getAllInfo",
		"nmFriendEx/getBlacklist",
		"nmGiftbag/reflushRecommend",
		"nmLogin/enterGame",
		"nmLogin/getUserList",
		"nmMerchant/getMerchant",
		"nmNews/getUnreadNewsCount",
		"nmPlanet/getPlanetInfo",
		"nmPlanet/getPlanets",
		"nmRecharge/getRechargeList",
		"nmTaskEx/getTaskList",
		"nmTimeActivity/getGiftBoxCount",
		"nmUser/getGuideState"
	);
	public function __construct($acccount,$account_id,$user_name,$user_id,$psw_clear,$password_hash){
		$this->crypt=new Crypt_RSA();
		$this->crypt->loadKey($this->RSA_KEY);
		$this->crypt->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
		// define constants
		$this->user["isJailbroken"]=0;
		$this->user["android_id"]="-1";
		$this->user["s_mac"]="-1";
		$this->user["mac"]="-1";
		$this->user["language"]="ru";
		$this->user["idfa"]="-1";
		$this->user["app_type_name"]="app";
		$this->user["s_adid"]="-1";
		$this->user["isPirated"]=0;
		$this->user["vendorId"]="-1";
		$this->user["sh1dId"]="-1";
		$this->user["md5dId"]="-1";
		$this->user["apns_token"]="-1";
		$this->user["nsuuId"]="-1";
		$this->user["terrace_type"]="google";
		$this->user["publish"]="google";
		$this->user['style']='android';
		$this->user['app_key']='ING004';
		$this->user["client_commit"]="0";
		// Static variables for device
		$this->user['device_id']="5f16e3d9-7700-4eae-92d0-5a3b6cf94b71";//can be one
		$this->user["advertising_id"]=$this->user['device_id'];
		$this->user["device_uid"]=$this->user['device_id'];
		$this->user["adId"]=$this->user['device_id'];
		$this->user["SAID"]=$this->user['device_id'];
		$this->user["device_type_name"]="VirtualBox Android 4.4.4";
		$this->user["device_detail_type"]="VirtualBox Android";
		$this->user["device_os_version"]="4.4.4";
		#$this->user["device_detail_type"]="Lenovo TAB 2 A10-70F";
		// Variables for game (can be changed by game)
		$this->user["sdk_ver"]="0191";
		#$this->user["spx_did"]=1452143;
		$this->user["spx_did"]=198165;
		$this->user["spx_id"]=$this->user["spx_did"];
		$this->user["server_id"]=91;
		$this->user["pkg_version"]="1.8.1";
		// Dynamic user variables
		$this->user['acccount']=$acccount; //account login
		$this->user['account_id']=$account_id; //account id (need find where to get it)
		$this->user['password_hash']=$password_hash;//clear psw hash for autologin, get after login
		$this->user['psw_clear']=$psw_clear;//clear passwd
		$this->user['psw']=$this->pub_crypt(strtoupper(md5($this->user['psw_clear'])));
		$this->user['account_key']=$this->pub_crypt($this->user['password_hash']);
		$this->user['client_id_clear']=strtoupper(md5(strtoupper(md5(rand(1,999999).time()."dajun"))));
		$this->user['client_id']=$this->pub_crypt($this->user['client_id_clear']);
		$this->user["client_key"]=$this->user['client_id'];
		$this->user['user_id']=$user_id;//commander id
		$this->user["user_name"]=$user_name;//commander name
		$this->user["session"]="";
		$this->user["token"]="";
		$this->user["planets"]=array();
		$this->user["remote_last_results"]["R_tick"]["update"]=Date("c");//this string better to be last
	}
	//***** PRIVATE FUNCTIONS ******
	private function pub_crypt ($text){
		$ciphertext = $this->crypt->encrypt($text);
	 	return base64_encode($ciphertext);
	}
	private function pub_decrypt ($ciphertext){
		$text = $this->crypt->decrypt($ciphertext);
	 	return $text;
	}
	private function common_data(){
		$common_data=array(
			"isJailbroken"=>$this->user["isJailbroken"],
			"android_id"=>$this->user["android_id"],
			"device_type_name"=>$this->user["device_type_name"],
			"s_mac"=>$this->user["s_mac"],
			"user_id"=>$this->user["user_id"],
			"sdk_ver"=>$this->user["sdk_ver"],
			"terrace_type"=>$this->user["terrace_type"],
			"device_detail_type"=>$this->user["device_detail_type"],
			"spx_did"=>$this->user["spx_did"],
			"advertising_id"=>$this->user['advertising_id'],
			"mac"=>$this->user["mac"],
			"language"=>$this->user["language"],
			"device_uid"=>$this->user['device_uid'],
			"idfa"=>$this->user["idfa"],
			"device_date"=>date("D M d H:i:s Y"),
			"app_type_name"=>$this->user["app_type_name"],
			"adId"=>$this->user['adId'],
			"server_id"=>$this->user["server_id"],
			"s_adid"=>$this->user["s_adid"],
			"isPirated"=>$this->user["isPirated"],
			"vendorId"=>$this->user["vendorId"],
			"user_name"=>$this->user["user_name"],
			"SAID"=>$this->user['SAID'],
			"sh1dId"=>$this->user["sh1dId"],
			"md5dId"=>$this->user["md5dId"],
			"pkg_version"=>$this->user["pkg_version"],
			"apns_token"=>$this->user["apns_token"],
			"nsuuId"=>$this->user["nsuuId"],
			"device_os_version"=>$this->user["device_os_version"]
		);
		return $common_data;
	}
	private function getSign($exdata){
		// can be at least 2 methods for SIGN
		// decrypted.auto_login_session + jsonstr(ex_data)+md5.client_key
		// 2 decrypted.auto_login_session + jsonstr(ex_data)
		$SIGN_PREPARE=$this->user["session"].json_encode($exdata,JSON_FORCE_OBJECT);
		$SIGN=strtoupper(md5($SIGN_PREPARE));
		return $SIGN;
	}
	private function open_url ($url,$data){
		//echo "url: $url\n";
		$curl=curl_init($url);
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		#curl_setopt($curl,CURLOPT_HTTPHEADER,array('Accept-Encoding: gzip'));
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');  
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}
	private function remote_api ($exdata){
		$debug=debug_backtrace();
		$func=$debug["1"]["function"];
		$url="http://".$this->hosts["ing"].$this->root_url."/".$this->urls[$func];
		$common_data=$this->common_data();
		$url=$url."?sign=".$this->getSign($exdata);
                $post_str="user_id=".$this->user["user_id"]."&user_name=".$this->user["user_name"]."&common_data=".json_encode($common_data)."&ex_data=".json_encode($exdata,JSON_FORCE_OBJECT)."&type=1";
		$res=$this->open_url($url,$post_str);
		$this->user["remote_last_results"][$func]["update"]=Date("c");
		$this->user["remote_last_results"][$func]["data"]=json_decode($res,true);
		if ($this->user["remote_last_results"][$func]["data"]["error"]!=0)
			die("error in request $func\n");
	}
	//***** PRIVATE FUNCTIONS - GAW REMOTE REQUESTS (for develop stage that functions are public) *****
	public function R_login (){//non standart request
		//login to game with clear user password
		$url="http://".$this->hosts["spx"].'/spx_account/index.php/api_account/login';
		$pd=array(
			"app_key"=>$this->user['app_key'],
			"acccount"=>$this->user['acccount'],
			"style"=>$this->user['style'],
			"psw"=>urlencode($this->user['psw']),
			"device_id"=>$this->user['device_id'],
			"client_key"=>urlencode($this->user['client_key'])
		);
		$res=$this->open_url($url,"pd=".json_encode($pd));
		$this->user["remote_last_results"]["R_login"]["update"]=Date("c");
		$this->user["remote_last_results"]["R_login"]["data"]=json_decode ($res,true);
	}
	public function R_auto_login (){//non standart request
		// day to day login with saved password hash 
		$url="http://".$this->hosts["spx"]."/spx_account/index.php/api_account/auto_login";
		$pd=array(
			"app_key"=>$this->user['app_key'],
			"account_key"=>urlencode($this->user['account_key']),
			"device_id"=>$this->user['device_id'],
			"style"=>$this->user['style'],
			"account_id"=>$this->user['account_id'],
			"client_id"=>urlencode($this->user['client_id'])
		);
		$res=$this->open_url($url,"pd=".json_encode($pd));
		$this->user["remote_last_results"]["R_auto_login"]["update"]=Date("c");
		$this->user["remote_last_results"]["R_auto_login"]["data"]=json_decode ($res,true);
		$this->user['token']=urldecode($this->user["remote_last_results"]["R_auto_login"]["data"]['token']);
		$this->user['session']=$this->pub_decrypt(base64_decode($this->user["remote_last_results"]["R_auto_login"]["data"]['session']));
	}
	public function R_getUserList (){//non standart request
		//get list of commanders for user
		$url="http://".$this->hosts["ing"].$this->root_url."/nmLogin/getUserList";
		$common_data=$this->common_data();
		$data=array(
			"app_key"=>$this->user["app_key"],
			"token"=>urlencode($this->user['token']),
			"publish"=>$this->user["publish"],
			"client_commit"=>$this->user["client_commit"],
			"server_id"=>$this->user["server_id"]
		);
		$res=$this->open_url($url,"common_data=".json_encode($common_data)."&data=".json_encode($data));
		$this->user["remote_last_results"]["R_getUserList"]["update"]=Date("c");
		$this->user["remote_last_results"]["R_getUserList"]["data"]=json_decode ($res,true);
	}
	public function R_enterGame (){//non standart request
		//enter to game for specific commander name
		$url="http://".$this->hosts["ing"].$this->root_url."/nmLogin/enterGame";
		$common_data=$this->common_data();
		$data=array(
			"server_id"=>$this->user["server_id"],
			"spx_id"=>$this->user["spx_id"],
			"token"=>$this->user['token'],
			"publish"=>$this->user["publish"],
			"client_commit"=>$this->user["client_commit"],
			"app_key"=>$this->user["app_key"],
			"language"=>$this->user["language"],
			"user_id"=>$this->user['user_id']
		);
		$post_str="common_data=".json_encode($common_data)."&data=".json_encode($data);
		$res=$this->open_url($url,$post_str);
		$this->user["remote_last_results"]["R_enterGame"]["update"]=Date("c");
		$this->user["remote_last_results"]["R_enterGame"]["data"]=json_decode($res,true);
		$this->user['account_id']=$this->user["remote_last_results"]["R_enterGame"]["data"]['account_id'];
	}
        public function R_tick (){
		//exdata clean
		$exdata=array();
		$this->remote_api($exdata);
	}
        public function R_getItemPrice (){
		//exdata clean
		$exdata=array();
		$this->remote_api($exdata);
	}
        public function R_getUnitConfig (){
		//exdata clean
		$exdata=array();
		$this->remote_api($exdata);
	}
        public function R_getUserData (){
		// exdata clean
		$exdata=array();
		$this->remote_api($exdata);
	}
        public function R_getAllInfo (){
		// fleets list in the fly (exdata clean)
		$exdata=array();
		$this->remote_api($exdata);
	}
        public function R_getUserPlanetList (){
		//get list of commander planets (exdata clean)
		$exdata=array();
		$this->remote_api($exdata);
	}
        public function R_getPlanetData ($planet){
		//get info about planet production
		$exdata=array(
			"planet_id"=>$planet
		);
		$this->remote_api($exdata);
	}
        public function R_cancelFleet ($fleetuid){
		//get list of commander planets (exdata clean)
		$exdata=array(
			"fleet_uid"=>$fleetuid
		);
		$this->remote_api($exdata);
	}
	public function R_getGameDataEx (){
		//get online info about user and game, dependency - getItemPrice
		//planet_id i think must take from first result of this function, but first time must be mother position
		if (isset($this->user["remote_last_results"]["R_getGameDataEx"]["data"])){
			$planet_id=
				$this->user["remote_last_results"]["R_getGameDataEx"]["data"]["position"]["0"]."_".
				$this->user["remote_last_results"]["R_getGameDataEx"]["data"]["position"]["1"]."_".
				$this->user["remote_last_results"]["R_getGameDataEx"]["data"]["position"]["2"];
			$tick=$this->user["remote_last_results"]["R_getGameDataEx"]["data"]["chat"]["tick"];
		}
		else{
			$planet_id=
				$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"]["0"]."_".
				$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"]["1"]."_".
				$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"]["2"];
			$tick="-1";
		}
		$exdata=array(
			"planet_id"=>$planet_id,//current position
			"item_config_version"=>$this->user["remote_last_results"]["R_getItemPrice"]["data"]["version"],// receive this data as value in function getItemPrice
			"count"=>20, // ?
			"tick"=>$tick, // this give understanding to server about readed last chat messages
			"language"=>$this->user["language"]
		);
		$this->remote_api($exdata);
	}
	public function R_sentFleet ($planets,$res_to_send,$ships_to_send){//need finish and check
		$ships=array(
			//есть подозрение что данные для отправки надо считать исходя из того что есть в наличии, тоесть если на планке есть ЛИ но мы не хотим отправлять их на атаку то надо явно указать 0, если же ЛИ на планке нет то ненадо их указывать вообще
			"1"=>0,//большой грузовик
			"0"=>0,
			"3"=>0,//тяжелый истребитель
			"2"=>0,
			"5"=>0,
			"4"=>0,
			"7"=>0,
			"6"=>0,
			"22"=>0,//супер груз
			"11"=>0,
			"10"=>0//зонд
		);
		$res=array (
			"0"=>0,//metal
			"2"=>0,//gas
			"1"=>0//krystal
		);
		foreach ($ships_to_send as $key => $value){
			$ships[$key]=$value;
		}
		foreach ($res_to_send as $key => $value){
			$res[$key]=$value;
		}
		$exdata=array(
			"end_pos"=>$planets["to"],//where attak, gal_sys_planet
			"purpose"=>8,// причина отправки, 7 - перемещение, 8 - атака
			"upshift"=>1,// ускорение 0 - без ускорения, 1 - 100% (газ)
			"bring_res"=>$res,
			"bring_ship"=>$ships,
			"rate"=>100,
			"start_pos"=>$planets["from"]//from where attack
		);
		$this->remote_api($exdata);
	}
	public function R_getUniverse ($gal,$sys){
		$exdata=array(
			"planet_id"=>-1,
			"sid"=>$sys,
			"language"=>$this->user["language"],
			"gid"=>$gal
		);
		$this->remote_api($exdata);
	}
	//***** PUBLIC FUNCTIONS -  API FOR OTHERS ******
	public function G_login (){
		if ($this->user["password_hash"]==""){
			$this->R_login();
			$this->user["password_hash"]=$this->user["remote_last_results"]["R_login"]["data"]["account_key"];
			$this->user['account_key']=$this->pub_crypt($this->user['password_hash']);
		}
		$this->R_auto_login();
		$this->R_enterGame();
		$this->R_getItemPrice();
		$this->R_getUserPlanetList();
		$this->G_ping_now();
	}
	public function G_ping_now(){
			$this->R_tick();
			$this->R_getGameDataEx();
	}
	public function G_ping(){
		$doping=false;
		if (strtotime("now")-strtotime($this->user["remote_last_results"]["R_tick"]['update'])>30)
			$doping=true;
		if ($doping==true){
			$this->R_tick();
			$this->R_getGameDataEx();
		}
	}
	public function G_updatePlanetsInfo($what_check,$delay){
		$planets=array();
		if ((is_string($what_check))and($what_check=="all")){
			foreach ($this->user['remote_last_results']['R_getUserPlanetList']['data']['planets'] as $val){
				$planets[]=$val['position'][0]."_".$val['position'][1]."_".$val['position'][2];
			}
		}
		else 
			$planets=$what_check;
		//need check if some active user is owner of some planet_id
		foreach ($planets as $planet_loc){
			$do=false;
			if (isset($this->user["planets"][$planet_loc]['update'])){
				if (strtotime("now")-strtotime($this->user["planets"][$planet_loc]['update'])>=$delay)
					$do=true;
			}
			else 
				$do=true;
			if ($do==true){
				$this->user["planets"][$planet_loc]["update"]=Date("c");
				$this->R_getPlanetData($planet_loc);
				$this->user["planets"][$planet_loc]["data"]=$this->user['remote_last_results']['R_getPlanetData']['data'];
			}
		}
	}
}
?>

