<?php
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
include('Crypt/RSA.php');


class GAW {
	public $DEBUG=4;// 1 - ERROR, 2 - WARNING, 3 - INFO, 4 - DEBUG1, 5 - DEBUG2, 6 - DEBUG3
	private $hosts=array(
		"spx"=>'54.183.10.158',                 // "/spx_*"
		"ing"=>'ing0042.sphinxjoy.net'         // "/ING*"
	);
	private $RSA_KEY="-----BEGIN RSA PUBLIC KEY-----\nMIGJAoGBAKv4OKlpY2oq9QZPMzAjbQfiqDqTnisSvdLP+mTswZJdbtk1J+4+qAyS\nJuZjSQljzcUu0ANg+QG0VsvoU72zu5pErZKWubfe9HB/tq69bhP60qgP6/W2VebW\nlqUNGtsMedxuVaFBL3SoqU7e5RELIsuArCJJIgz86BQDX0x63VpXAgMBAAE=\n-----END RSA PUBLIC KEY-----";
	private $crypt;
	public $user=array();
	private $root_url="/ING004/n/WebServer/Web/sogame/newControl";
	private $fcookie='cookie.txt';
	private $db_dir='/var/lib/dokuwiki/data/pages';
	private $savers_file='/var/lib/dokuwiki/data/pages/mult_autosave.txt';
	private $psw_file='/var/lib/dokuwiki/data/pages/gaw_logins.txt';
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
		"R_getPlanetData"=>"nmPlanet/getPlanetData",// - 38 show info abount planet res
		"R_getPlanetInfo"=>"nmPlanet/getPlanetInfo",// - 38 show detail abount planet res (maybe this one switch to planet)
		"R_getSpacecraft"=>"nmUnit/getSpacecraft",// open Spacecraft (Kosmoverf)
		"R_product"=>"nmUnit/product", // make new ships 
		"R_getFrientList"=>"nmFriendEx/getFrientList", // friends list
		"R_getRequestList"=>"nmFriendEx/getRequestList", // list friends new requests
		"R_acceptFriend"=>"nmFriendEx/acceptFriend", // accept new friend
		"R_applyUnion"=>"nmFleet/applyUnion", //invite member to attack
		"R_getActivityList"=>"nmActivity/getActivityList", //list of presents
		"R_getActivityReward"=>"nmActivity/getActivityReward",// take available presents
		"R_getRadarFleets"=>"nmFleet/getRadarFleets", // get fleets in radar
		"R_getInviteUnionFleets"=>"nmFleet/getInviteUnionFleets", // list friend requests fleet for save
		"R_agreeUnionFleet"=>"nmFleet/agreeUnionFleet", // save friend fleet
		"nmBuild/refresh",
		"nmPlanet/getPlanetInfo",// - 33 switch to planet
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
		"nmPlanet/getPlanets",
		"nmRecharge/getRechargeList",
		"nmTaskEx/getTaskList",
		"nmTimeActivity/getGiftBoxCount",
		"nmUser/getGuideState"
	);
	public function __construct($user_name,$first_login=false){
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
		#$this->user['device_id']="5f16e3d9-7700-4eae-92d0-5a3b6cf94b71";//can be one
		$this->user['device_id']="";
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
		$this->user['account_id']=""; //account id (need find where to get it)
		//$this->user['account_id']=$account_id; //account id (need find where to get it)
		$this->user['password_hash']="";//clear psw hash for autologin, get after login
		$this->user['psw_clear']="";//clear passwd
		$this->user['psw']="";// crypted password
		$this->user['account_key']="";
		#$this->user['account_key']=$this->_pub_crypt($this->user['password_hash']);
		$this->user['client_id_clear']=strtoupper(md5(strtoupper(md5(rand(1,999999).time()."dajun"))));
		$this->user['client_id']=$this->_pub_crypt($this->user['client_id_clear']);
		$this->user["client_key"]=$this->user['client_id'];
		$this->user['user_id']="";//commander id
		if ($first_login==false){
			$this->user["user_name"]=$user_name;//commander name
			$this->user['acccount']=""; //account login
		}
		else{
			$this->user["user_name"]="";//commander name
			$this->user['acccount']=$user_name; //account login
		}
		$this->user["presents"]="";
		$this->user["session"]="";
		$this->user["token"]="";
		$this->user["mother"]="";
		$this->user["planets"]=array();
		$this->user["planets_for_work"]=array();
		$this->user["remote_last_results"]["R_tick"]["update"]=Date("c");//this string better to be last
		exec('grep " \* " '.$this->savers_file.' | grep -v "wrap em" | sed -e "s/^[\* ]\{1,\}//g" | sed -e "s/ .*$//g"',$this->user['savers_users']);
		$this->_get_login_password();
		$this->_cookie_read();
	}
	//***** PRIVATE FUNCTIONS ******
	private function _pub_crypt ($text){
		$ciphertext = $this->crypt->encrypt($text);
	 	return base64_encode($ciphertext);
	}
	private function _pub_decrypt ($ciphertext){
		$text = $this->crypt->decrypt($ciphertext);
	 	return $text;
	}
	private function _common_data(){
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
	private function _getSign($exdata){
		// can be at least 2 methods for SIGN
		// decrypted.auto_login_session + jsonstr(ex_data)+md5.client_key
		// 2 decrypted.auto_login_session + jsonstr(ex_data)
		$SIGN_PREPARE=$this->user["session"].json_encode($exdata,JSON_FORCE_OBJECT);
		$SIGN=strtoupper(md5($SIGN_PREPARE));
		return $SIGN;
	}
	private function _open_url ($url,$data){
		if ($this->DEBUG>=5) echo "url: $url\n";
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
	private function _analyze_error ($func){
		$error=$this->user["remote_last_results"][$func]["data"]["error"];
		if (($error!=0)and($error != 1229))
			die("error in request $func \n".
				"GET: ".json_encode($this->user["remote_last_results"][$func]["request"]["get"])."\n".
				"POST: ".json_encode($this->user["remote_last_results"][$func]["request"]["post"])."\n".
				"RES: ".json_encode($this->user["remote_last_results"][$func]["data"])."\n");
	}
	private function _remote_api ($exdata){
		$debug=debug_backtrace();
		$func=$debug["1"]["function"];
		if ($this->DEBUG>=4) echo "CALL: $func\n";
		$url="http://".$this->hosts["ing"].$this->root_url."/".$this->urls[$func];
		$common_data=$this->_common_data();
		$url=$url."?sign=".$this->_getSign($exdata);
                $post_str="user_id=".$this->user["user_id"]."&user_name=".$this->user["user_name"]."&common_data=".json_encode($common_data)."&ex_data=".json_encode($exdata,JSON_FORCE_OBJECT)."&type=1";
		$res=$this->_open_url($url,$post_str);
		$this->user["remote_last_results"][$func]["update"]=Date("c");
		$this->user["remote_last_results"][$func]["request"]["get"]=$url;
		$this->user["remote_last_results"][$func]["request"]["post"]=$post_str;
		$this->user["remote_last_results"][$func]["data"]=json_decode($res,true);
		$this->user["remote_last_results"][$func]["raw"]=$res;
		$this->_analyze_error($func);
	}
	private function _cookie_read(){
		//read account id and pwd hash from cookie file
		if(file_exists($this->fcookie)){
			$data=json_decode(file_get_contents($this->fcookie),true);
			if ((isset($data[$this->user['acccount']]['account_id']))and($data[$this->user['acccount']]['password_hash'])){
				if (($data[$this->user['acccount']]['account_id']!="null")and($data[$this->user['acccount']]['password_hash']!="null")){
					$this->user['account_id']=$data[$this->user['acccount']]['account_id'];
					$this->user['password_hash']=$data[$this->user['acccount']]['password_hash'];
					$this->user['account_key']=$this->_pub_crypt($this->user['password_hash']);
				}
			}
			if (isset($data[$this->user['acccount']][$this->user['user_name']]['presents']))
				$this->user['presents']=$data[$this->user['acccount']][$this->user['user_name']]['presents'];
			if (!is_numeric($this->user['presents']))
				$this->user['presents']=-1;
			if (isset($data[$this->user['acccount']][$this->user['user_name']]['device_id']))
				$this->user['device_id']=$data[$this->user['acccount']][$this->user['user_name']]['device_id'];
			if ($this->user['device_id']=="")
				$this->user['device_id']=exec("cat /proc/sys/kernel/random/uuid");
			$this->user["advertising_id"]=$this->user['device_id'];
			$this->user["device_uid"]=$this->user['device_id'];
			$this->user["adId"]=$this->user['device_id'];
			$this->user["SAID"]=$this->user['device_id'];
			return true;
		}
		return false;
	}
	private function _cookie_save(){
		//save cookie, if need update or set pwd hash (need do after R_login)
		if(file_exists($this->fcookie))
			$data=json_decode(file_get_contents($this->fcookie),true);
		$data[$this->user['acccount']]['account_id']=$this->user['account_id'];
		$data[$this->user['acccount']]['password_hash']=$this->user['password_hash'];
		$data[$this->user['acccount']][$this->user['user_name']]['presents']=$this->user['presents'];
		$data[$this->user['acccount']][$this->user['user_name']]['device_id']=$this->user['device_id'];
		file_put_contents($this->fcookie,json_encode($data));
		return true;
	}
	public function _get_login_password(){
		//get login name by Commander name
		if ($this->user['acccount']==""){
			$this->user['acccount']=exec ('basename $(grep -l "'.$this->user['user_name'].'" '.$this->db_dir.'/users/*) | sed -e "s/\.txt$//g"');
		}
		$this->user['psw_clear']=exec ('grep -i "'.$this->user['acccount'].']]" '.$this->psw_file.' | cut -d"|" -f 3 | sed -e "s/<decrypt>\(.*\)<\/decrypt>/\1/" | openssl enc -d -aes-256-cbc -a -k psw');
		$this->user['psw']=$this->_pub_crypt(strtoupper(md5($this->user['psw_clear'])));
	}
	public function _is_user_online($user_name){
		foreach($this->user['remote_last_results']['R_getFrientList']['data']['friends'] as $val){
			if ($val['user_name']==$user_name){
				if ($val['heart_beat']<=60)
					return $val['user_id'];
				else
					return 0;
			}
		}
		return -1;
	}
	//***** PRIVATE FUNCTIONS - GAW REMOTE REQUESTS (for develop stage that functions are public) *****
	public function R_login (){//non standart request
		//login to game with clear user password
		if ($this->DEBUG>=4) echo "CALL: R_login\n";
		$url="http://".$this->hosts["spx"].'/spx_account/index.php/api_account/login';
		$pd=array(
			"app_key"=>$this->user['app_key'],
			"acccount"=>$this->user['acccount'],
			"style"=>$this->user['style'],
			"psw"=>urlencode($this->user['psw']),
			"device_id"=>$this->user['device_id'],
			"client_key"=>urlencode($this->user['client_key'])
		);
		$res=$this->_open_url($url,"pd=".json_encode($pd));
		$ares=json_decode($res,true);
		if ($ares['error_code']=="1001"){
			echo "Error password for login ".$this->user['acccount'];
		}
		$this->user["account_id"]=$ares['account_id'];
		$this->user["password_hash"]=$ares['account_key'];
		$this->user['account_key']=$this->_pub_crypt($this->user['password_hash']);
		$this->user["remote_last_results"]["R_login"]["update"]=Date("c");
		$this->user["remote_last_results"]["R_login"]["data"]=$ares;
	}
	public function R_auto_login (){//non standart request
		// day to day login with saved password hash 
		$ret=true;
		if ($this->DEBUG>=4) echo "CALL: R_auto_login\n";
		$url="http://".$this->hosts["spx"]."/spx_account/index.php/api_account/auto_login";
		$pd=array(
			"app_key"=>$this->user['app_key'],
			"account_key"=>urlencode($this->user['account_key']),
			"device_id"=>$this->user['device_id'],
			"style"=>$this->user['style'],
			"account_id"=>$this->user['account_id'],
			"client_id"=>urlencode($this->user['client_id'])
		);
		$res=$this->_open_url($url,"pd=".json_encode($pd));
		$this->user["remote_last_results"]["R_auto_login"]["update"]=Date("c");
		$this->user["remote_last_results"]["R_auto_login"]["data"]=json_decode ($res,true);
		if ($this->user["remote_last_results"]["R_auto_login"]["data"]['error_code']=="1001"){
			$ret=false;
			echo "Error password for login ".$this->user['acccount'];
		}
		$this->user['token']=urldecode($this->user["remote_last_results"]["R_auto_login"]["data"]['token']);
		$this->user['session']=$this->_pub_decrypt(base64_decode($this->user["remote_last_results"]["R_auto_login"]["data"]['session']));
		return $ret;
	}
	public function R_getUserList (){//non standart request
		//get list of commanders for user
		if ($this->DEBUG>=4) echo "CALL: R_getUserList\n";
		$url="http://".$this->hosts["ing"].$this->root_url."/nmLogin/getUserList";
		$common_data=$this->_common_data();
		$data=array(
			"app_key"=>$this->user["app_key"],
			"token"=>urlencode($this->user['token']),
			"publish"=>$this->user["publish"],
			"client_commit"=>$this->user["client_commit"],
			"server_id"=>$this->user["server_id"]
		);
		$res=$this->_open_url($url,"common_data=".json_encode($common_data)."&data=".json_encode($data));
		$this->user["remote_last_results"]["R_getUserList"]["update"]=Date("c");
		$this->user["remote_last_results"]["R_getUserList"]["data"]=json_decode ($res,true);
	}
	public function R_enterGame (){//non standart request
		//enter to game for specific commander name (getUserList first required)
		if ($this->DEBUG>=4) echo "CALL: R_enterGame\n";
		$url="http://".$this->hosts["ing"].$this->root_url."/nmLogin/enterGame";
		$common_data=$this->_common_data();
		foreach ($this->user["remote_last_results"]["R_getUserList"]["data"]["users"] as $val){
			if ($val["user_name"]==$this->user["user_name"]){
				$this->user["user_id"]=$val["user_id"];
				break;
			}
		}
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
		$res=$this->_open_url($url,$post_str);
		$this->user["remote_last_results"]["R_enterGame"]["update"]=Date("c");
		$this->user["remote_last_results"]["R_enterGame"]["data"]=json_decode($res,true);
		#$this->user['account_id']=$this->user["remote_last_results"]["R_enterGame"]["data"]['account_id'];
	}
        public function R_tick (){
		//exdata clean
		$exdata=array();
		$this->_remote_api($exdata);
	}
        public function R_getItemPrice (){
		//exdata clean
		$exdata=array();
		$this->_remote_api($exdata);
	}
        public function R_getUnitConfig (){
		//exdata clean
		$exdata=array();
		$this->_remote_api($exdata);
	}
        public function R_getUserData (){
		// exdata clean
		$exdata=array();
		$this->_remote_api($exdata);
	}
        public function R_getAllInfo (){
		// fleets list in the fly (exdata clean)
		$exdata=array();
		$this->_remote_api($exdata);
	}
        public function R_getUserPlanetList (){
		//get list of commander planets (exdata clean)
		$exdata=array();
		$this->_remote_api($exdata);
	}
        public function R_getFrientList (){
		//get list of commander planets (exdata clean)
		$exdata=array();
		$this->_remote_api($exdata);
	}
        public function R_getSpacecraft ($planet){
		//get info about planet production
		$exdata=array(
			"planet_id"=>$planet
		);
		$this->_remote_api($exdata);
		$this->user["planets"][$planet]["spacecraft"]["update"]=Date("c");
		$this->user["planets"][$planet]["spacecraft"]["data"]=$this->user["remote_last_results"]["R_getSpacecraft"]["data"]["data"];
	}
        public function R_getPlanetInfo ($planet){
		//get info about planet production
		$exdata=array(
			"planet_id"=>$planet
		);
		$this->_remote_api($exdata);
	}
        public function R_getPlanetData ($planet){
		//get info about planet production
		$exdata=array(
			"planet_id"=>$planet
		);
		$this->_remote_api($exdata);
	}
        public function R_cancelFleet ($fleetuid){
		//get list of commander planets (exdata clean)
		$exdata=array(
			"fleet_uid"=>$fleetuid
		);
		$this->_remote_api($exdata);
	}
        public function R_getRadarFleets ($planet=false){
		//get attakers on radar
		if ($planet==false)
			$planet=$this->user['mother'];
		$exdata=array(
			"planet_id"=>$planet
		);
		$this->_remote_api($exdata);
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
		$this->_remote_api($exdata);
	}
	public function R_sentFleet ($purpose,$planets,$res_to_send,$ships_to_send){//need finish and check
		$ships=array(
			//есть подозрение что данные для отправки надо считать исходя из того что есть в наличии, тоесть если на планке есть ЛИ но мы не хотим отправлять их на атаку то надо явно указать 0, если же ЛИ на планке нет то ненадо их указывать вообще
			//"1"=>0,//большой грузовик
			//"0"=>0,
			//"3"=>0,//тяжелый истребитель
			//"2"=>0,
			//"5"=>0,
			//"4"=>0,
			//"7"=>0,
			//"6"=>0,
			//"22"=>0,//супер груз
			//"11"=>0,
			//"10"=>0//зонд
		);
		$res=array (
			"0"=>0,//metal
			"2"=>0,//gas
			"1"=>0//krystal
		);
		foreach ($ships_to_send as $key => $value){
			$ships[$key]=intval($value);
		}
		foreach ($res_to_send as $key => $value){
			$res[$key]=intval($value);
		}
		$exdata=array(
			"end_pos"=>$planets["to"],//where attak, gal_sys_planet
			"purpose"=>$purpose,// причина отправки, 1 - возврат (только при показе), 7 - перемещение, 8 - атака, 9 - видел пока незнаю (возможно совместка)
			"upshift"=>1,// ускорение 0 - без ускорения, 1 - 100% (газ)
			"bring_res"=>$res,
			"bring_ship"=>$ships,
			"rate"=>100,
			"start_pos"=>$planets["from"]//from where attack
		);
		$this->_remote_api($exdata);
	}
	public function R_getUniverse ($gal,$sys){
		$exdata=array(
			"planet_id"=>-1,
			"sid"=>$sys,
			"language"=>$this->user["language"],
			"gid"=>$gal
		);
		$this->_remote_api($exdata);
	}
        public function R_product ($planet,$count,$unit_id){
		//get info about planet production
		$exdata=array(
			"planet_id"=>$planet,
			"count"=>intval($count),
			"unit_id"=>$unit_id
		);
		$this->_remote_api($exdata);
	}
	public function R_applyUnion ($fleetid,$user_id){
		//invite member to attack
		//for now only 1 can me invited
		$exdata=array(
			"fleet_uid"=>$fleetid,
			"target_user_id_array"=>array(
				"0"=>$user_id
			)
		);
		$this->_remote_api($exdata);
	}
	public function R_getActivityList(){
		//get list of presents
		$exdata=array(
			"skip"=>0,
			"language"=>$this->user["language"],
			"limit"=>0
		);
		$this->_remote_api($exdata);
	}
	public function R_getActivityReward($activity_id){
		$mother=
			$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][0]."_".
			$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][1]."_".
			$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][2];
		$exdata=array(
			"planet_id"=>$mother,
			"language"=>$this->user["language"],
			"activity_id"=>$activity_id
		);
		$this->_remote_api($exdata);
	}
	public function R_getRequestList(){
		// list friends new requests
		$exdata=array();
		$this->_remote_api($exdata);
	}
	public function R_acceptFriend($user_id){
		// accept new friend
		$exdata=array(
			"target_id"=>$user_id
		);
		$this->_remote_api($exdata);
	}
	public function R_getInviteUnionFleets(){
		// list friend requests fleet for save
		$exdata=array(
			"planet_id"=>$this->user['mother']
		);
		$this->_remote_api($exdata);
	}
	public function R_agreeUnionFleet($planet_from,$fleet_uid){
		// save friend fleet
		// for now this used only for saves
		$exdata=array(
			"planet_id"=>$planet_from,
			"upshift"=>0,
			"bring_ship"=>array(
				"9"=>1
			),
			"rate"=>10,
			"fleet_uid"=>$fleet_uid
		);
		$this->_remote_api($exdata);
	}
	//***** PUBLIC FUNCTIONS -  API FOR OTHERS ******
	public function G_login (){
		//$this->_get_login_password(); ## moved to constructor
		if (($this->user["password_hash"]=="")or($this->user["password_hash"]=="null")){
			//first login
			$this->R_login();
			$this->_cookie_save();
		}
		if (!$this->R_auto_login()){
			//wrong password
			$this->R_login();
			$this->_cookie_save();
			if (!$this->R_auto_login())
				die ("Cannot login, maybe wrong password\n");
		}
		$this->R_getUserList();
		if ($this->user["user_name"]!=""){
			$this->R_enterGame();
			$this->R_getItemPrice();
			$this->R_getUserPlanetList();
			$this->user["mother"]=
				$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][0]."_".
				$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][1]."_".
				$this->user["remote_last_results"]["R_getUserPlanetList"]["data"]["mother_position"][2];
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
		}
		else 
			$planets=$what_check;
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
		else 
			$planets=$what_check;
		//need check if some active user is owner of some planet_id
		foreach ($planets as $planet_loc){
			$this->user["planets"][$planet_loc]["spacecraft"]["update"]=Date("c");
			$this->R_getSpacecraft($planet_loc);
			$this->user["planets"][$planet_loc]["spacecraft"]["data"]=$this->user['remote_last_results']['R_getSpacecraft']['data'];
		}
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
		$kk_max=intval((($ships[1]*25000)+($ships[22]*75000)+($ships[23]*40000))/1000000);
		$planets["to"]=$pirates;
		$planets["from"]=$mother;
		if ($with_res){
			$gas=$this->user["planets"][$mother]["info"]["data"]["res"][2]["now"]-300000;
			if ($gas<0)$gas=0;
				$res=array(
					"0"=>intval($this->user["planets"][$mother]["info"]["data"]["res"][0]["now"]),
					"1"=>intval($this->user["planets"][$mother]["info"]["data"]["res"][1]["now"]),
					"2"=>intval($gas)
			);
		}else{
			$res=array("0"=>0,"1"=>0,"2"=>0);
		}
		$kk_res=intval(($res[0]+$res[1]+$res[2])/1000000);
		$kk_p=intval($kk_res*100/$kk_max);
		$this->R_sentFleet(8,$planets,$res,$ships);
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
		if (!isset($this->user['remote_last_results']['R_getActivityList']))
			$this->R_getActivityList();
		//find minimal overplus
		$overplus_min=999999;
		foreach ($this->user['remote_last_results']['R_getActivityList']['data']['activity'] as $val){
			if ($val['overplus']<$overplus_min)
				$overplus_min=$val['overplus'];
		}
		$next=time($this->user['remote_last_results']['R_getActivityList']['update'])+$overplus_min;
		echo Date("c")." overplus_min:$overplus_min , last ".$this->user['remote_last_results']['R_getActivityList']['update']." , next ".Date("c",$next)."\n";
		//check if update + overplus_min <= now then new update
		if (strtotime($this->user['remote_last_results']['R_getActivityList']['update'])+$overplus_min<=time()){
			$this->R_getActivityList();
		}
		//look and take where overplus=0 and new update
		$took=false;
		foreach ($this->user['remote_last_results']['R_getActivityList']['data']['activity'] as $val){
			if ($val['overplus']==0){
				echo "take present number ".$val['receive_id']."\n";
				$this->R_getActivityReward($val['activity_id']);
				$took=true;
			}
		}
		if ($took)
			$this->R_getActivityList();
		if ($overplus_min==999999)
			$this->user["presents"]=0;
		else
			$this->user["presents"]=count($this->user['remote_last_results']['R_getActivityList']['data']['activity']);
	}
	public function G_Exit(){
		$this->_cookie_save();
	}
}
?>

