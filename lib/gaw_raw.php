<?php
set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');
include('Crypt/RSA.php');


class GAW_RAW {
	private $hosts=array(
		"spx"=>'54.183.10.158',                 // "/spx_*"
		"ing"=>'ing0042.sphinxjoy.net'         // "/ING*"
	);
	private $RSA_KEY="-----BEGIN RSA PUBLIC KEY-----\nMIGJAoGBAKv4OKlpY2oq9QZPMzAjbQfiqDqTnisSvdLP+mTswZJdbtk1J+4+qAyS\nJuZjSQljzcUu0ANg+QG0VsvoU72zu5pErZKWubfe9HB/tq69bhP60qgP6/W2VebW\nlqUNGtsMedxuVaFBL3SoqU7e5RELIsuArCJJIgz86BQDX0x63VpXAgMBAAE=\n-----END RSA PUBLIC KEY-----";
	private $crypt;
	public $db;
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
		"R_autoGame"=>"nmLogin/autoGame",
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
		"R_getPlanetQuardInfo"=>"nmFleet/getPlanetQuardInfo", //?
		"R_pushResBank" =>"nmBuild/pushResBank", //
		"R_overLooker"=>"nmFleet/overLooker",
		"R_getMailList"=>"nmMail/getMailList",
		"R_getMailInfo"=>"nmMail/getMailInfo",
		"R_applyQuard"=>"nmFleet/applyQuard",//invite friend to local planet
		"R_getItemCountInfo"=>"nmItem/getItemCountInfo",//list items
		"R_useItem"=>"nmItem/useItem",//use items
		"R_setUserName"=>"nmUser/setUserName",
		"R_upgrade"=>"nmBuild/upgrade",//upgrade building
		"R_degrade"=>"nmBuild/degrade",//degrade building
		"R_finishUpgrade"=>"nmBuild/finishUpgrade",//finish upgrade building
		"R_finishDegrade"=>"nmBuild/finishDegrade",//finish upgrade building
		"R_getBackTime"=>"nmFleet/getBackTime",
		"R_getGalaxyGroupInfo"=>"nmGalaxy/getGalaxyGroupInfo",
		"R_createUser"=>"nmLogin/createUser",
		//fast
		"nmBuild/refresh",
		"nmPlanet/getPlanetInfo",// - 33 switch to planet
		// maybe need
		"nmActive/getActiveDescInfo",
		"nmActivity/getEvent",
		"nmAllianceTec/getTecsInfo",
		"nmControl/getBlueprintsConfig",
		"nmControl/openAllianceControl",
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
	public function __construct($user_name=false,$first_login=false){
		$this->db=pg_connect("host=localhost port=5432 dbname=gaw user=gaw password=gaw") or die('connection to db failed');
		$this->crypt=new Crypt_RSA();
		$this->crypt->loadKey($this->RSA_KEY);
		$this->crypt->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
		$this->Init($user_name,$first_login);
		$this->_get_savers();
	}
	//***** PRIVATE FUNCTIONS ******
	private function _init_pre(){
		$this->user['acccount']="";
		$this->user['user_name']="";
		$this->user['online']="";
		$this->user['game_data']=array(
			"sdk_ver"=>"0201",
			"spx_did"=>198165,
			"spx_id"=>"",
			"server_id"=>101,//101 -oracle, 118 - apolon
			"pkg_version"=>"1.8.1",
			"publish"=>"google",
			'style'=>'android',
			'app_key'=>'ING004',
			"client_commit"=>"0",
			"isJailbroken"=>0,
			"android_id"=>"-1",
			"s_mac"=>"-1",
			"terrace_type"=>"google",
			"mac"=>"-1",
			"language"=>"ru",
			"idfa"=>"-1",
			"app_type_name"=>"app",
			"s_adid"=>"-1",
			"isPirated"=>"0",
			"vendorId"=>"-1",
			"sh1dId"=>"-1",
			"md5dId"=>"-1",
			"apns_token"=>"-1",
			"nsuuId"=>"-1",
			'device_id'=>"",//_init_device - post generation device uid
			"advertising_id"=>"",// set to device_id in _init_device
			"device_uid"=>"",// set to device_id in _init_device
			"adId"=>"",// set to device_id in _init_device
			"SAID"=>"",// set to device_id in _init_device
			"device_type_name"=>"VB Android 5.1.0",
			"device_detail_type"=>"VB Android 5.1.0",
			"device_os_version"=>"5.1.0",
			'account_id'=>"", //account id (need find where to get it)
			'password_hash'=>"",//clear psw hash for autologin, get after login
			'psw_clear'=>"",//clear passwd
			'psw'=>"",// crypted password
			'account_key'=>"",
			'user_id'=>"",//commander id
			"session"=>"",
			"token"=>""
		);
		$this->user['game_data']["spx_id"]=$this->user['game_data']["spx_did"];
		$this->user['remote_last_results']=array();
		$this->user['savers_users']=array();
		$this->user["mother"]="";
		$this->user["planets"]=array();
		$this->user["planets_for_work"]=array();
		$this->user["presents"]="";
	}
	private function _init_post(){
		//init basic data in update username/account
		$this->user['game_data']['client_id_clear']=strtoupper(md5(strtoupper(md5(rand(1,999999).time()."dajun"))));
		$this->user['game_data']['client_id']=$this->_pub_crypt($this->user['game_data']['client_id_clear']);
		$this->user['game_data']["client_key"]=$this->user['game_data']['client_id'];
		$this->user["remote_last_results"]["R_tick"]["update"]=Date("c");//this string better to be last
		$this->_get_login_password();
		$this->_is_online();
		//$this->_cookie_read();//move this future to DB
	}
	private function _init_device(){
		$res=pg_query($this->db,"select device_id from users where user_name='".$this->user['user_name']."';");
		$resf=pg_fetch_array($res,NULL);
		$device_id=$resf['device_id'];
		if ($device_id==""){
			$this->user['game_data']['device_id']=exec("cat /proc/sys/kernel/random/uuid");
			$device_id=$this->user['game_data']['device_id'];
			if ($this->user['user_name']!="")
				pg_query($this->db,"update users set device_id='".$device_id."' where user_name='".$this->user['user_name']."';");
		}else 
			$this->user['game_data']['device_id']=$device_id;
		$this->user['game_data']["advertising_id"]=$this->user['game_data']['device_id'];
		$this->user['game_data']["device_uid"]=$this->user['game_data']['device_id'];
		$this->user['game_data']["adId"]=$this->user['game_data']['device_id'];
		$this->user['game_data']["SAID"]=$this->user['game_data']['device_id'];
	}
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
			"isJailbroken"=>$this->user["game_data"]["isJailbroken"],
			"android_id"=>$this->user["game_data"]["android_id"],
			"device_type_name"=>$this->user["game_data"]["device_type_name"],
			"s_mac"=>$this->user["game_data"]["s_mac"],
			"user_id"=>$this->user["game_data"]["user_id"],
			"sdk_ver"=>$this->user["game_data"]["sdk_ver"],
			"terrace_type"=>$this->user["game_data"]["terrace_type"],
			"device_detail_type"=>$this->user["game_data"]["device_detail_type"],
			"spx_did"=>$this->user["game_data"]["spx_did"],
			"advertising_id"=>$this->user["game_data"]['advertising_id'],
			"mac"=>$this->user["game_data"]["mac"],
			"language"=>$this->user["game_data"]["language"],
			"device_uid"=>$this->user["game_data"]['device_uid'],
			"idfa"=>$this->user["game_data"]["idfa"],
			"device_date"=>date("D M d H:i:s Y"),
			"app_type_name"=>$this->user["game_data"]["app_type_name"],
			"adId"=>$this->user["game_data"]['adId'],
			"server_id"=>$this->user["game_data"]["server_id"],
			"s_adid"=>$this->user["game_data"]["s_adid"],
			"isPirated"=>$this->user["game_data"]["isPirated"],
			"vendorId"=>$this->user["game_data"]["vendorId"],
			"user_name"=>$this->user["user_name"],
			"SAID"=>$this->user["game_data"]['SAID'],
			"sh1dId"=>$this->user["game_data"]["sh1dId"],
			"md5dId"=>$this->user["game_data"]["md5dId"],
			"pkg_version"=>$this->user["game_data"]["pkg_version"],
			"apns_token"=>$this->user["game_data"]["apns_token"],
			"nsuuId"=>$this->user["game_data"]["nsuuId"],
			"device_os_version"=>$this->user["game_data"]["device_os_version"]
		);
		return $common_data;
	}
	private function _getSign($exdata){
		// can be at least 2 methods for SIGN
		// decrypted.auto_login_session + jsonstr(ex_data)+md5.client_key
		// 2 decrypted.auto_login_session + jsonstr(ex_data)
		$SIGN_PREPARE=$this->user['game_data']["session"].json_encode($exdata,JSON_FORCE_OBJECT);
		$SIGN=strtoupper(md5($SIGN_PREPARE));
		return $SIGN;
	}
	private function _open_url ($url,$data,$func_name){
		$this->user["remote_last_results"][$func_name]["update"]=Date("c");
		$this->user["remote_last_results"][$func_name]["request"]["get"]=$url;
		$this->user["remote_last_results"][$func_name]["request"]["post"]=$data;
		if ($this->DEBUG>=5) echo "url: $url\n";
		$curl=curl_init($url);
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		#curl_setopt($curl,CURLOPT_HTTPHEADER,array('Accept-Encoding: gzip'));
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');  
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curl);
		curl_close($curl);
		$this->user["remote_last_results"][$func_name]["data"]=json_decode($response,true);
		$this->user["remote_last_results"][$func_name]["raw"]=$response;
		return $response;
	}
	private function _analyze_error ($func){
		$error=$this->user["remote_last_results"][$func]["data"]["error"];
		if (($error!=0)and($error != 1229)){
			//print_r($this->user);
			if ($this->EXIT_ON_ERROR==true)
				die("error in request $func \n".
					"GET: ".json_encode($this->user["remote_last_results"][$func]["request"]["get"])."\n".
					"POST: ".json_encode($this->user["remote_last_results"][$func]["request"]["post"])."\n".
					"RES: ".json_encode($this->user["remote_last_results"][$func]["data"])."\n");
		}
	}
	private function _remote_api ($exdata){
		$debug=debug_backtrace();
		$func=$debug["1"]["function"];
		if ($this->DEBUG>=4) echo date("c")." CALL: $func\n";
		$url="http://".$this->hosts["ing"].$this->root_url."/".$this->urls[$func];
		$common_data=$this->_common_data();
		$url=$url."?sign=".$this->_getSign($exdata);
                $post_str="user_id=".$this->user['game_data']["user_id"]."&user_name=".$this->user["user_name"]."&common_data=".json_encode($common_data)."&ex_data=".json_encode($exdata,JSON_FORCE_OBJECT)."&type=1";
		$res=$this->_open_url($url,$post_str,$func);
		$this->_analyze_error($func);
	}
	public function _setup_password(){
		if ($this->user['game_data']['password_hash']!="")
			$this->user['game_data']['account_key']=$this->_pub_crypt($this->user['game_data']['password_hash']);
		//$this->user['psw_clear']=exec ('grep -i "'.$this->user['acccount'].']]" '.$this->psw_file.' | head -1 | cut -d"|" -f 3 | sed -e "s/<decrypt>\(.*\)<\/decrypt>/\1/" | openssl enc -d -aes-256-cbc -a -k psw');
		$this->user['game_data']['psw']=$this->_pub_crypt(strtoupper(md5($this->user['game_data']['psw_clear'])));
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
	public function _is_user_online($user_name){
		//must be decomised
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
	public function _is_online(){
		$res=pg_query($this->db,"select online from bot0 where user_name='".$this->user['user_name']."';");
		$resf=pg_fetch_array($res,NULL);
		$online=$resf['online'];
		if ($online == 't')
			$this->user['online']=true;
		else
			$this->user['online']=false;
	}
	public function _get_savers(){
		$res=pg_query($this->db,'select user_name from savers where enabled=true;');
		$this->user['savers_users']=pg_fetch_array($res,NULL,PGSQL_NUM);
		//old
		//exec('grep " \* " '.$this->savers_file.' | grep -v "wrap em" | sed -e "s/^[\* ]\{1,\}//g" | sed -e "s/ .*$//g"',$this->user['savers_users']);
	}
	public function _sync_planets_to_db (){
		//update info about planets in db
		foreach($this->user['planets'] as $pos => $d){
			$size=json_encode(array("now"=>$d['size']['now'],"max"=>$d['size']['max']));
			if ($pos==$this->user['mother']) $m="true";
			else $m="false";
			pg_query("insert into planets 
					(position,planet_name,mother,user_name,temp,size,skin_id) 
				values ('".$pos."','".$d['name']."',$m,'".$this->user['user_name']."',".$d['temperature'].",'$size',".$d['skin_id'].")
				on conflict (position) 
				do update set last_list_update=now(),planet_name='".$d['name']."',mother=$m,user_name='".$this->user['user_name']."',temp=".$d['temperature'].",size='$size',skin_id=".$d['skin_id'].";");
			if (isset($d['info']['data'])){
				$i=$d['info']['data'];
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
	//***** PUBLIC FUNCTIONS *****//
	public function Init($user_name=false,$first_login=false){
		$this->user=array();
		$this->_init_pre();
		$this->user['acccount']=""; //account login
		$this->user["user_name"]="";//commander name
		if ($user_name!=false){
			// Dynamic user variables
			#$this->user['account_key']=$this->_pub_crypt($this->user['password_hash']);
			if ($first_login==false){
				$this->user["user_name"]=$user_name;//commander name
				$res=pg_query($this->db,"select acccount from users where user_name='".$this->user["user_name"]."';");
				$resf=pg_fetch_array($res,NULL);
				$this->user['acccount']=$resf['acccount']; //account login
				if ($this->user['acccount']=="")
					die("[ERROR] cannot find login/password for user ".$this->user['user_name']."\n");
				//and get acccount for user name
			}
			else{
				$this->user["user_name"]="";//commander name
				$this->user['acccount']=$user_name; //account login
			}
			$this->_init_device();
			$this->_init_post();
		}
	}
	//***** PRIVATE FUNCTIONS - GAW REMOTE REQUESTS (for develop stage that functions are public) *****
	public function R_reg (){//create new login
		//create new login
		if ($this->DEBUG>=4) echo "CALL: R_reg\n";
		$url="http://".$this->hosts["spx"].'/spx_account/index.php/api_account/reg';
		$pd=array(
			"app_key"=>$this->user['game_data']['app_key'],
			"acccount"=>$this->user['acccount'],
			"style"=>$this->user['game_data']['style'],
			"psw"=>urlencode($this->user['game_data']['psw']),
			"mail"=>"qwe@qwe.ru",
			"device_id"=>$this->user['game_data']['device_id'],
			"client_key"=>urlencode($this->user['game_data']['client_key'])
		);
		$res=$this->_open_url($url,"pd=".json_encode($pd),'R_reg');
		$ares=json_decode($res,true);
	}
	public function R_login (){//non standart request
		//login to game with clear user password
		if ($this->DEBUG>=4) echo "CALL: R_login\n";
		$url="http://".$this->hosts["spx"].'/spx_account/index.php/api_account/login';
		$pd=array(
			"app_key"=>$this->user['game_data']['app_key'],
			"acccount"=>$this->user['acccount'],
			"style"=>$this->user['game_data']['style'],
			"psw"=>urlencode($this->user['game_data']['psw']),
			"device_id"=>$this->user['game_data']['device_id'],
			"client_key"=>urlencode($this->user['game_data']['client_key'])
		);
		$res=$this->_open_url($url,"pd=".json_encode($pd),'R_login');
		$ares=json_decode($res,true);
		if ($ares['error_code']!="1"){
			echo "Error in login ".$this->user['acccount']." error code: ".$ares['error_code'].", ".$ares['error_desc']."\n";
		}else{
			$this->user['game_data']["account_id"]=$ares['account_id'];
			$this->user['game_data']["password_hash"]=$ares['account_key'];
			$this->user['game_data']['account_key']=$this->_pub_crypt($this->user['game_data']['password_hash']);
		}
	}
	public function R_auto_login (){//non standart request
		// day to day login with saved password hash 
		$ret=true;
		if ($this->DEBUG>=4) echo "CALL: R_auto_login\n";
		$url="http://".$this->hosts["spx"]."/spx_account/index.php/api_account/auto_login";
		$pd=array(
			"app_key"=>$this->user['game_data']['app_key'],
			"account_key"=>urlencode($this->user['game_data']['account_key']),
			"device_id"=>$this->user['game_data']['device_id'],
			"style"=>$this->user['game_data']['style'],
			"account_id"=>$this->user['game_data']['account_id'],
			"client_id"=>urlencode($this->user['game_data']['client_id'])
		);
		$res=$this->_open_url($url,"pd=".json_encode($pd),'R_auto_login');
		if ($this->user["remote_last_results"]["R_auto_login"]["data"]['error_code']=="1001"){
			$ret=false;
			echo "Error password for login ".$this->user['acccount'];
		}
		$this->user['game_data']['token']=urldecode($this->user["remote_last_results"]["R_auto_login"]["data"]['token']);
		$this->user['game_data']['session']=$this->_pub_decrypt(base64_decode($this->user["remote_last_results"]["R_auto_login"]["data"]['session']));
		return $ret;
	}
	public function R_getUserList (){//non standart request
		//get list of commanders for user
		if ($this->DEBUG>=4) echo "CALL: R_getUserList\n";
		$url="http://".$this->hosts["ing"].$this->root_url."/nmLogin/getUserList";
		$common_data=$this->_common_data();
		$data=array(
			"app_key"=>$this->user['game_data']["app_key"],
			"token"=>urlencode($this->user['game_data']['token']),
			"publish"=>$this->user['game_data']["publish"],
			"client_commit"=>$this->user['game_data']["client_commit"],
			"server_id"=>$this->user['game_data']["server_id"]
		);
		$res=$this->_open_url($url,"common_data=".json_encode($common_data)."&data=".json_encode($data),'R_getUserList');
		foreach($this->user['remote_last_results']['R_getUserList']['data']['users'] as $aru)
			if ($aru['user_name']==$this->user['user_name'])
				$this->user['game_data']['user_id']=$aru['user_id'];
	}
	public function R_enterGame (){//non standart request
		//enter to game for specific commander name (getUserList first required)
		if ($this->DEBUG>=4) echo "CALL: R_enterGame\n";
		$url="http://".$this->hosts["ing"].$this->root_url."/nmLogin/enterGame";
		$common_data=$this->_common_data();
		foreach ($this->user["remote_last_results"]["R_getUserList"]["data"]["users"] as $val){
			if ($val["user_name"]==$this->user["user_name"]){
				$this->user['game_data']["user_id"]=$val["user_id"];
				#$this->user['game_data']["user_id"]="20217399084";
				#$this->user['game_data']["user_id"]="20217399084";
				break;
			}
		}
		$data=array(
			"server_id"=>"-".$this->user['game_data']["server_id"],
			"spx_id"=>$this->user['game_data']["spx_id"],
			"token"=>$this->user['game_data']['token'],
			"publish"=>$this->user['game_data']["publish"],
			"client_commit"=>$this->user['game_data']["client_commit"],
			"app_key"=>$this->user['game_data']["app_key"],
			"language"=>$this->user['game_data']["language"],
			"user_id"=>$this->user['game_data']['user_id']
		);
		$post_str="common_data=".json_encode($common_data)."&data=".json_encode($data);
		$res=$this->_open_url($url,$post_str,'R_enterGame');
		
		//$this->user['account_id']=$this->user["remote_last_results"]["R_enterGame"]["data"]['account_id'];
	}
        public function R_tick (){
		//exdata clean
		$exdata=array();
		$this->_remote_api($exdata);
	}
        public function R_createUser (){
		//create User
		//вроде бы создаёт пользователя только первоначального, для нового зарегестрированного аккаунта, при повторных вызовах возвращает -1 и сесию не создаёт
		if ($this->DEBUG>=4) echo "CALL: R_createUser\n";
		$url="http://".$this->hosts["ing"].$this->root_url."/nmLogin/createUser";
		$common_data=$this->_common_data();
		$data=array(
			"server_id"=>$this->user['game_data']["server_id"],
			"token"=>urlencode($this->user['game_data']['token']),
			"publish"=>$this->user['game_data']["publish"],
			"client_commit"=>$this->user['game_data']["client_commit"],
			"language"=>$this->user['game_data']["language"],
			"app_key"=>$this->user['game_data']["app_key"],
		);
		$res=$this->_open_url($url,"common_data=".json_encode($common_data)."&data=".json_encode($data),'R_createUser');
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
        public function R_getBackTime ($fleetuid){
		// ?
		$exdata=array(
			"fleet_uid"=>$fleetuid
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
			"language"=>$this->user['game_data']["language"]
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
			"language"=>$this->user['game_data']["language"],
			"gid"=>$gal
		);
		$this->_remote_api($exdata);
	}
        public function R_getMailList (){
		//
		$exdata=array();
		$this->_remote_api($exdata);
	}
        public function R_getMailInfo ($mail_uid,$type){
		//
		$exdata=array(
			"mail_uid"=>$mail_uid,
			"type"=>$type
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
			"language"=>$this->user['game_data']["language"],
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
			"language"=>$this->user['game_data']["language"],
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
        public function R_overLooker ($planet){
		// ?
		$exdata=array(
			"planet_id"=>$planet
		);
		$this->_remote_api($exdata);
	}
        public function R_getPlanetQuardInfo ($planet){
		// ?
		$exdata=array(
			"planet_id"=>$planet
		);
		$this->_remote_api($exdata);
	}
        public function R_pushResBank ($planet,$res_id,$count){
		// ?
		$exdata=array(
			"planet_id"=>$planet,
			"res_id"=>$res_id,
			"count"=>$count
		);
		$this->_remote_api($exdata);
	}
        public function R_degrade ($planet,$build){
		// upgrade build
		$exdata=array(
			"planet_id"=>$planet,
			"build_id"=>$build
		);
		$this->_remote_api($exdata);
	}
        public function R_upgrade ($planet,$build){
		// upgrade build
		$exdata=array(
			"planet_id"=>$planet,
			"build_id"=>$build
		);
		$this->_remote_api($exdata);
	}
        public function R_applyQuard ($planet,$user_id){
		// invite user to local planet
		$exdata=array(
			"planet_id"=>$planet,
			"target_user_id_array"=>array(
				"0"=>$user_id
			)
		);
		$this->_remote_api($exdata);
	}
	public function R_getItemCountInfo (){
		$exdata=array();
		$this->_remote_api($exdata);
	}
	public function R_useItem ($id,$count,$planet,$ex_id=-1){
		//use item from box
		if ($ex_id==-1)
			$exdata=array(
				"id"=>$id,
				"count"=>$count,
				"planet_id"=>$planet
			);
		else
			$exdata=array(
				"planet_id"=>$planet,
				"count"=>$count,
				"role_object"=>1,
				"id"=>$id,
				"ex_id"=>$ex_id
			);
		$this->_remote_api($exdata);
	}
	public function R_setUserName($newname){
		$exdata=array(
			"new_name"=>$newname
		);
		$this->_remote_api($exdata);
	}
	public function R_finishUpgrade($planet,$build){
		$exdata=array(
			"planet_id"=>$planet,
			"build_id"=>$build
		);
		$this->_remote_api($exdata);
	}
	public function R_finishDegrade($planet,$build){
		$exdata=array(
			"planet_id"=>$planet,
			"build_id"=>$build
		);
		$this->_remote_api($exdata);
	}
	public function R_getGalaxyGroupInfo($gal,$group){
		// 3 display of map
		$exdata=array(
			"galaxy_id"=>$gal,
			"group_id"=>$group
		);
		$this->_remote_api($exdata);
	}
}
?>

