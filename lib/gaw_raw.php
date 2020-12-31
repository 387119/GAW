<?php
define ("G_CALL",0);
define ("G_INFO",1);
define ("G_NOTICE",2);
define ("G_WARNING",3);
define ("G_ERROR",4);
define ("G_FATAL",5);

set_include_path(dirname(__FILE__).'/phpseclib');
require('Crypt/RSA.php');
require('Crypt/RC4.php');
/*
	Входящие параметры которые может принимать R_Init на вход
	
	acccount - Название аккаунта (для логина по чистому паролю)
	psw_clear -  Пароль от аккаунта (для логина по чистому паролю)
	account_id - ID аккаунта (для автологина)
	password_hash - хеш пароля (для автологина)
	user_name - имя пользователя в которого надо войти
	device_id - девайс, если не указан то генерируется автоматом
	os_name - название ОС девайса, если не указанна используется статическая
	os_version - версия ОС девайса, если не указанна используется статическая
	server_id - сервер на который надо зайти, обязательно к указанию

	** для логина достаточно для указания или acccount+psw_clear или account_id+password_hash, указывать обе конструкции необязательно
*/
class GAW_RAW {
	public $DEBUG=4;
	public $config=array(
			"auto_presents"=>true
		);
	private $hosts=array(
		"root"=>'api.sphinxjoy.net',                 // "/spx_root*"
		"spx"=>'api.sphinxjoy.net',                 // "/spx*"
		#"spx"=>'54.183.10.158',                 // "/spx_*"
		"ing"=>'ing0042.sphinxjoy.net'         // "/ING*"
	);
	private $root_url=array(
		"root"=>"/spx_root/index.php",
		"spx"=>"/spx_account/index.php",
		"ing"=>"/ING004/n/WebServer/Web/sogame/newControl"
	);
	private $RSA_KEY="-----BEGIN RSA PUBLIC KEY-----\nMIGJAoGBAKv4OKlpY2oq9QZPMzAjbQfiqDqTnisSvdLP+mTswZJdbtk1J+4+qAyS\nJuZjSQljzcUu0ANg+QG0VsvoU72zu5pErZKWubfe9HB/tq69bhP60qgP6/W2VebW\nlqUNGtsMedxuVaFBL3SoqU7e5RELIsuArCJJIgz86BQDX0x63VpXAgMBAAE=\n-----END RSA PUBLIC KEY-----";
	private $crypt;
	private $rc4_key="A3aSeSf2+bo503b3k0YKeU0PSSt7GXJgfzTPoU0H7iMkz1NmRag5AvszoCEKQ9Tnf1k2cNtQ/qT2/nU6CxbenK7OJxLj5Fjy1f78Y5uttudQPhkUGesjJD7+3h1qKHTH";
	private $rc4;
	public $user=array();
	public function __construct(){
		$this->__init_RSA();
		$this->__init_pre();
	}
	//***** PRIVATE FUNCTIONS ******
	private function __init_RSA(){
		$this->crypt=new Crypt_RSA();
		$this->crypt->loadKey($this->RSA_KEY);
		$this->crypt->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
	}
	private function __init_RC4(){
		$this->rc4=new Crypt_RC4();
		$this->rc4->setKey($this->rc4_key.$this->user['game_data']['client_id_clear']);
	}
	private function __init_pre(){
		$this->user=array();
		$this->user['online']="";
		$this->user['game_data']=array(
			"acccount"=>"",
			'account_id'=>"", //account id (need find where to get it)
			"user_name"=>"",
			'user_id'=>"",//commander id
			"sdk_ver"=>"0222",//"0214",
			"content_version"=>"0222",//equal to sdk_ver
			"spx_did"=>0,//198165,
			"spx_id"=>"",
			"server_id"=>"-1",
			"pkg_version"=>"1.8.1",//"1.9.6",
			"app_version"=>"1.8.1",// should be equal to pkg_version
			"publish"=>"google",
			'style'=>'android',
			'app_key'=>'ING004',
			"client_commit"=>"0",
			"isJailbroken"=>0,
			"android_id"=>"-1",
			"ios_idfa"=>"-1",
			"mac_address"=>"-1",
			"platform"=>"android",
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
			"gp_adid"=>"",//_init_device
			"advertising_id"=>"",// set to device_id in _init_device
			"device_uid"=>"",// set to device_id in _init_device
			"adId"=>"",// set to device_id in _init_device
			"SAID"=>"",// set to device_id in _init_device
			"device_type_name"=>"WX 5.1",
			"device_type"=>"WX 5.1",//equal to device_type_name
			"device_detail_type"=>"WX",
			"device_os_version"=>"5.1",
			"os_version"=>"5.1",//equal to device_os_version
			'password_hash'=>"",//clear psw hash for autologin, get after login
			'psw_clear'=>"",//clear passwd
			'psw'=>"",// crypted password
			'account_key'=>"",
			"session"=>"",
			"token"=>""
		);
		$this->user['game_data']["spx_id"]=$this->user['game_data']["spx_did"];
		$this->user['remote']=array();
		$this->user["mother"]="";
		$this->user["planets"]=array();
		$this->user["presents"]="";
		$this->user['savers']=array();
		$this->user["planets_for_work"]=array();
	}
	private function __init_post(){
		$this->user['game_data']['client_id_clear']=strtoupper(md5(strtoupper(md5(rand(1,999999).time()."dajun"))));
		$this->user['game_data']['client_id']=$this->__pub_crypt($this->user['game_data']['client_id_clear']);
		$this->user['game_data']["client_key"]=$this->user['game_data']['client_id'];
		$this->__reinit();
	}
	private function __reinit (){
		$this->user["remote"]["nmUser/tick"]["update"]=Date("c",time()-120);//this string better to be last
		$this->__init_RC4();
	}
	private function __init_device(){
		//$device_id=$this->user['game_data']['device_id'];
		if ($this->user['game_data']['device_id']=="")
			$this->user['game_data']['device_id']=exec("cat /proc/sys/kernel/random/uuid");
		$this->user['game_data']["advertising_id"]=$this->user['game_data']['device_id'];
		$this->user['game_data']["device_uid"]=$this->user['game_data']['device_id'];
		$this->user['game_data']["adId"]=$this->user['game_data']['device_id'];
		$this->user['game_data']["SAID"]=$this->user['game_data']['device_id'];
	}
	private function __pub_crypt ($text){
		$ciphertext = $this->crypt->encrypt($text);
	 	return base64_encode($ciphertext);
	}
	private function __pub_decrypt ($ciphertext){
		$text = $this->crypt->decrypt($ciphertext);
	 	return $text;
	}
	private function __base64_decode_rc4 ($ciphertext){
		$text=$this->rc4->decrypt(base64_decode($ciphertext));
		return $text;
	}
	private function __common_data(){
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
			#"device_uid"=>$this->user["game_data"]['device_uid'],
			"device_uid"=>"-1",
			"idfa"=>$this->user["game_data"]["idfa"],
			"device_date"=>date("D M d H:i:s Y"),
			"app_type_name"=>$this->user["game_data"]["app_type_name"],
			"adId"=>"-1",
			#"adId"=>$this->user["game_data"]['adId'],
			"server_id"=>$this->user["game_data"]["server_id"],
			"s_adid"=>$this->user["game_data"]["s_adid"],
			"isPirated"=>$this->user["game_data"]["isPirated"],
			"vendorId"=>$this->user["game_data"]["vendorId"],
			"user_name"=>$this->user["game_data"]["user_name"],
			#"SAID"=>$this->user["game_data"]['SAID'],
			"SAID"=>"-1",
			"sh1dId"=>$this->user["game_data"]["sh1dId"],
			"md5dId"=>$this->user["game_data"]["md5dId"],
			"pkg_version"=>$this->user["game_data"]["pkg_version"],
			"apns_token"=>$this->user["game_data"]["apns_token"],
			"nsuuId"=>$this->user["game_data"]["nsuuId"],
			"device_os_version"=>$this->user["game_data"]["device_os_version"]
		);
		if ($common_data['user_id']=="")
			$common_data['user_id']=0;
		return $common_data;
	}
	private function __getSign($exdata){
		// can be at least 2 methods for SIGN
		// decrypted.auto_login_session + jsonstr(ex_data)+md5.client_key
		// 2 decrypted.auto_login_session + jsonstr(ex_data)
		$SIGN_PREPARE=$this->user['game_data']["session"].json_encode($exdata,JSON_FORCE_OBJECT).$this->user['game_data']['client_id_clear'];
		#$SIGN_PREPARE=$this->user['game_data']["session"].json_encode($exdata,JSON_FORCE_OBJECT).$this->user['game_data']['client_id_clear'].$this->user['game_data']['client_id_clear'];
		$SIGN=strtoupper(md5($SIGN_PREPARE));
		return $SIGN;
	}
	private function __open_url ($url,$data){
		$debug=debug_backtrace();
		$remote=$debug["1"]["args"][0];
		if ($this->DEBUG>=4) 
			$this->R_Log(G_CALL,$remote);
		$this->user["remote"][$remote]["update"]=Date("c");
		$this->user["remote"][$remote]["request"]["get"]=$url;
		$this->user["remote"][$remote]["request"]["post"]=$data;
		$this->user["remote"][$remote]["response"]["data"]="";
		$this->user["remote"][$remote]["response"]["raw"]="";
		if ($this->DEBUG>=5) echo "url: $url\n";
		$curl=curl_init($url);
		curl_setopt($curl,CURLOPT_POST,true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		#curl_setopt($curl,CURLOPT_HTTPHEADER,array('Accept-Encoding: gzip'));
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');  
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10); //timeout in seconds
		$proxy=getenv('HTTP_PROXY');
		if (($proxy!=false)and(strlen($proxy)>0)){
			curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 0);
			curl_setopt($curl, CURLOPT_PROXY, $proxy);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0);
		}
		$response = curl_exec($curl);
		curl_close($curl);
		#$this->user["remote"][$remote]["response"]["data"]=json_decode($response,true);
		$this->user["remote"][$remote]["response"]["raw"]=$response;
		$this->__remote_post_decrypt($remote);
		$this->user["remote"][$remote]["response"]["data"]=json_decode(substr($this->user["remote"][$remote]["response"]["raw"],strpos($this->user["remote"][$remote]["response"]["raw"],'{"')),true);
	}
	private function __analyze_error (){
		$debug=debug_backtrace();
		$remote=$debug["1"]["args"][0];
		if (isset($this->user["remote"][$remote]["response"]["data"]["error"])){
			$error=$this->user["remote"][$remote]["response"]["data"]["error"];
			if (($error!=0)and($error != 1229)){
				//print_r($this->user);
				if ($this->EXIT_ON_ERROR==true)
					die("error in request $remote \n".
						"GET: ".json_encode($this->user["remote"][$remote]["request"]["get"])."\n".
						"POST: ".json_encode($this->user["remote"][$remote]["request"]["post"])."\n".
						"RES: ".json_encode($this->user["remote"][$remote]["response"]["data"])."\n");
			}
		}
	}
	public function __setup_password(){
		if ($this->user['game_data']['password_hash']!="")
			$this->user['game_data']['account_key']=$this->__pub_crypt($this->user['game_data']['password_hash']);
		//$this->user['psw_clear']=exec ('grep -i "'.$this->user['game_data']['acccount'].']]" '.$this->psw_file.' | head -1 | cut -d"|" -f 3 | sed -e "s/<decrypt>\(.*\)<\/decrypt>/\1/" | openssl enc -d -aes-256-cbc -a -k psw');
		$this->user['game_data']['psw']=$this->__pub_crypt(strtoupper(md5($this->user['game_data']['psw_clear'])));
	}
	public function __remote_post_decrypt($remote){
		switch ($remote){
			case "nmAlliance/getMemberList":
			case "nmUniverse/getUniverse":
			case "nmFleet/getRadarFleets":
			case "nmFriendEx/getFrientList":
				$this->user["remote"][$remote]["response"]["raw_crypt"]=$this->user["remote"][$remote]["response"]["raw"];
				$this->user["remote"][$remote]["response"]["raw"]=$this->__base64_decode_rc4($this->user["remote"][$remote]["response"]["raw_crypt"]);
				break;
		}
	}
	public function __remote_pre_set($remote,$data){
		switch ($remote){
			case "api_root/spx_init":
				if (!isset($data['pd']['app_key']))
					$data['pd']['app_key']=$this->user['game_data']['app_key'];
				if (!isset($data['pd']['spx_did']))
					$data['pd']['spx_did']=$this->user['game_data']['spx_did'];
				if (!isset($data['pd']['publish']))
					$data['pd']['publish']=$this->user['game_data']['publish'];
				if (!isset($data['pd']['device']['gp_adid']))
					$data['pd']['device']['gp_adid']=$this->user['game_data']['device_id'];
				if (!isset($data['pd']['device']['android_id']))
					$data['pd']['device']['android_id']=$this->user['game_data']['android_id'];
				if (!isset($data['pd']['device']['ios_idfa']))
					$data['pd']['device']['ios_idfa']=$this->user['game_data']['ios_idfa'];
				if (!isset($data['pd']['device']['mac_address']))
					$data['pd']['device']['mac_address']=$this->user['game_data']['mac_address'];
				if (!isset($data['pd']['device']['platform']))
					$data['pd']['device']['platform']=$this->user['game_data']['platform'];
				if (!isset($data['pd']['info']['app_version']))
					$data['pd']['info']['app_version']=$this->user['game_data']['app_version'];
				if (!isset($data['pd']['info']['os_version']))
					$data['pd']['info']['os_version']=$this->user['game_data']['os_version'];
				if (!isset($data['pd']['info']['content_version']))
					$data['pd']['info']['content_version']=$this->user['game_data']['content_version'];
				if (!isset($data['pd']['info']['platform']))
					$data['pd']['info']['platform']=$this->user['game_data']['platform'];
				if (!isset($data['pd']['info']['device_type']))
					$data['pd']['info']['device_type']=$this->user['game_data']['device_type'];
				break;
			case "api_account/auto_login":
				if (!isset($data['pd']['app_key']))
					$data['pd']['app_key']=$this->user['game_data']['app_key'];
				if (!isset($data['pd']['account_key']))
					$data['pd']['account_key']=urlencode($this->user['game_data']['account_key']);
				if (!isset($data['pd']['device_id']))
					$data['pd']['device_id']=$this->user['game_data']['device_id'];
				if (!isset($data['pd']['style']))
					$data['pd']['style']=$this->user['game_data']['style'];
				if (!isset($data['pd']['account_id']))
					$data['pd']['account_id']=$this->user['game_data']['account_id'];
				if (!isset($data['pd']['client_id']))
					$data['pd']['client_id']=urlencode($this->user['game_data']['client_id']);//ранее использовался мной client_id, возможно єта часть влияет на тип подписи SIGN
				break;
			case "api_account/login":
			case "api_account/reg":
				if (!isset($data['pd']['app_key']))
					$data['pd']['app_key']=$this->user['game_data']['app_key'];
				if (!isset($data['pd']['acccount']))
					$data['pd']['acccount']=$this->user['game_data']['acccount'];
				if (!isset($data['pd']['style']))
					$data['pd']['style']=$this->user['game_data']['style'];
				if (!isset($data['pd']['psw']))
					$data['pd']['psw']=urlencode($this->user['game_data']['psw']);
				if (!isset($data['pd']['mail']))
					$data['pd']['mail']=urlencode($this->user['game_data']['mail']);
				if (!isset($data['pd']['device_id']))
					$data['pd']['device_id']=$this->user['game_data']['device_id'];
				if (!isset($data['pd']['client_key']))
					$data['pd']['client_key']=urlencode($this->user['game_data']['client_key']);
				break;
			case "nmLogin/getUserList":
				if (!isset($data['data']['app_key']))
					$data['data']['app_key']=$this->user['game_data']['app_key'];
				if (!isset($data['data']['token']))
					$data['data']['token']=urlencode($this->user['game_data']['token']);
				if (!isset($data['data']['publish']))
					$data['data']['publish']=$this->user['game_data']['publish'];
				if (!isset($data['data']['client_commit']))
					$data['data']['client_commit']=$this->user['game_data']['client_commit'];
				if (!isset($data['data']['server_id']))
					$data['data']['server_id']=$this->user['game_data']['server_id'];
				break;
			case "nmLogin/createUser":
				if (!isset($data['data']['server_id']))
					$data['data']['server_id']=$this->user['game_data']['server_id'];
				if (!isset($data['data']['client_secret']))
					$data['data']['client_secret']=$this->user['game_data']['client_id_clear'];
				if (!isset($data['data']['token']))
					$data['data']['token']=$this->user['game_data']['token'];
				if (!isset($data['data']['publish']))
					$data['data']['publish']=$this->user['game_data']['publish'];
				if (!isset($data['data']['spx_id']))
					$data['data']['spx_id']=$this->user['game_data']['spx_id'];
				if (!isset($data['data']['client_commit']))
					$data['data']['client_commit']=$this->user['game_data']['client_commit'];
				if (!isset($data['data']['language']))
					$data['data']['language']=$this->user['game_data']['language'];
				if (!isset($data['data']['app_key']))
					$data['data']['app_key']=$this->user['game_data']['app_key'];
				break;
			case "nmLogin/enterGame":
				if (!isset($data['data']['app_key']))
					$data['data']['app_key']=$this->user['game_data']['app_key'];
				if (!isset($data['data']['client_secret']))
					$data['data']['client_secret']=$this->user['game_data']['client_id_clear'];
				if (!isset($data['data']['user_id']))
					$data['data']['user_id']=$this->user['game_data']['user_id'];
				if (!isset($data['data']['client_commit']))
					$data['data']['client_commit']=$this->user['game_data']['client_commit'];
				if (!isset($data['data']['publish']))
					$data['data']['publish']=$this->user['game_data']['publish'];
				if (!isset($data['data']['server_id']))
					$data['data']['server_id']=$this->user['game_data']['server_id'];
				if (!isset($data['data']['spx_id']))
					$data['data']['spx_id']=$this->user['game_data']['spx_id'];
				if (!isset($data['data']['language']))
					$data['data']['language']=$this->user['game_data']['language'];
				if (!isset($data['data']['token']))
					$data['data']['token']=$this->user['game_data']['token'];
				break;
			case "nmItem/useItem":
				if ((isset($data['ex_data']['ex_id']))and(!isset($data['ex_data']['role_object'])))
					$data['ex_data']['role_object']=1;
				break;
			case "nmUser/getGameDataEx":
				if (!isset($data['ex_data']['planet_id']))
					$data['ex_data']['planet_id']=$this->user['mother'];
				if (!isset($data['ex_data']['item_config_version']))
					$data['ex_data']['item_config_version']=$this->user["remote"]["nmItem/getItemPrice"]["response"]["data"]["version"];
				if (!isset($data['ex_data']['count']))
					$data['ex_data']['count']=20;
				if (!isset($data['ex_data']['tick']))
					$data['ex_data']['tick']='-1';
				if (!isset($data['ex_data']['language']))
					$data['ex_data']['language']=$this->user['game_data']["language"];
				break;
			case "nmActivity/getActivityReward":
				if (!isset($data['ex_data']['planet_id']))
					$data['ex_data']['planet_id']=$this->user['mother'];
				if (!isset($data['ex_data']['language']))
					$data['ex_data']['language']=$this->user['game_data']["language"];
				break;
			case "nmFleet/getRadarFleets":
			case "nmFleet/getInviteUnionFleets":
				if (!isset($data['ex_data']['planet_id']))
					$data['ex_data']['planet_id']=$this->user['mother'];
				break;
			case "nmUniverse/getUniverse":
				if (!isset($data['ex_data']['planet_id']))
					$data['ex_data']['planet_id']="-1";
				if (!isset($data['ex_data']['language']))
					$data['ex_data']['language']=$this->user['game_data']['language'];
				break;
				
		}
		return $data;
	}
	public function __remote_post_set($remote){
		switch ($remote){
			case "api_root/spx_init":
				$this->user['game_data']['spx_did']=$this->user["remote"][$remote]['response']["data"]['spx_did'];
				break;
			case "api_account/reg":
				$this->user['game_data']['token']=urldecode($this->user["remote"][$remote]['response']["data"]['token']);
				$this->user['game_data']['session']=$this->__pub_decrypt(base64_decode($this->user["remote"][$remote]['response']["data"]['session']));
			case "api_account/login":
				$this->user['game_data']["account_id"]=$this->user["remote"][$remote]['response']["data"]['account_id'];
				$this->user['game_data']["password_hash"]=$this->user["remote"][$remote]['response']["data"]['account_key'];
				$this->user['game_data']['account_key']=$this->__pub_crypt($this->user['game_data']['password_hash']);
				break;
			case "api_account/auto_login":
				$this->user['game_data']['token']=urldecode($this->user["remote"][$remote]['response']["data"]['token']);
				$this->user['game_data']['session']=$this->__pub_decrypt(base64_decode($this->user["remote"][$remote]['response']["data"]['session']));
				break;
			case "nmLogin/createUser":
				$this->user['game_data']['user_id']=$this->user["remote"][$remote]['response']["data"]['user_id'];
				break;
			case "nmLogin/getUserList":
				foreach ($this->user["remote"]["nmLogin/getUserList"]["response"]["data"]["users"] as $val){
					if ($this->user['game_data']['user_id']!="")
						if ($val["user_id"]==$this->user['game_data']["user_id"]){
							$this->user["game_data"]["user_name"]=$val["user_name"];
							break;
						}
					if ($this->user['game_data']['user_name']!="")
						if ($val["user_name"]==$this->user["game_data"]["user_name"]){
							$this->user['game_data']["user_id"]=$val["user_id"];
							break;
						}
				}
				break;
			case "nmUser/getUserPlanetList":
				$this->user['planets']=array();
				$this->user['mother']=
					$this->user["remote"]["nmUser/getUserPlanetList"]["response"]["data"]["mother_position"]["0"]."_".
					$this->user["remote"]["nmUser/getUserPlanetList"]["response"]["data"]["mother_position"]["1"]."_".
					$this->user["remote"]["nmUser/getUserPlanetList"]["response"]["data"]["mother_position"]["2"];
				foreach ($this->user['remote']['nmUser/getUserPlanetList']['response']['data']['planets'] as $val){
					$this->user['planets'][$val['position'][0]."_".$val['position'][1]."_".$val['position'][2]]['list']['update']=Date("c");
					$this->user['planets'][$val['position'][0]."_".$val['position'][1]."_".$val['position'][2]]['list']['data']=$val;
				}
				break;
			case "nmPlanet/getPlanetInfo":
				$pos=$this->user['remote']['nmPlanet/getPlanetInfo']['response']['data']['position'][0]."_".
					$this->user['remote']['nmPlanet/getPlanetInfo']['response']['data']['position'][1]."_".
					$this->user['remote']['nmPlanet/getPlanetInfo']['response']['data']['position'][2];
				$this->user["planets"][$pos]["info"]["update"]=Date("c");
				$this->user["planets"][$pos]["info"]["data"]=$this->user['remote']['nmPlanet/getPlanetInfo']['response']['data'];
				break;
			case "nmUnit/getSpacecraft":
				$pos=$this->user['remote']['nmUnit/getSpacecraft']['response']['data']['position'][0]."_".
					$this->user['remote']['nmUnit/getSpacecraft']['response']['data']['position'][1]."_".
					$this->user['remote']['nmUnit/getSpacecraft']['response']['data']['position'][2];
				$this->user["planets"][$pos]["spacecraft"]["update"]=Date("c");
				$this->user["planets"][$pos]["spacecraft"]["data"]=$this->user['remote']['nmUnit/getSpacecraft']['response']['data'];
				break;
			case "nmUser/tick":
		}
	}
	//***** PUBLIC FUNCTIONS *****//
	public function R_Init($vars){
		$this->__init_pre();
		if (($vars['acccount']=='')or(($vars['psw_clear']=='')and($vars['password_hash']==''))){
			die('account and password clear/password hash are not set, please setup first');
		}
		if (isset($vars['acccount']))
			$this->user['game_data']['acccount']=$vars['acccount'];
		if (isset($vars['account_id']))
			$this->user['game_data']['account_id']=$vars['account_id'];
		if (isset($vars['psw_clear']))
			$this->user['game_data']['psw_clear']=$vars['psw_clear'];
		if (isset($vars['password_hash']))
			$this->user['game_data']['password_hash']=$vars['password_hash'];
		if (isset($vars['user_name']))
			$this->user["game_data"]['user_name']=$vars['user_name'];
		if (isset($vars['user_id']))
			$this->user['game_data']['user_id']=$vars['user_id'];
		if (isset($vars['email']))
			$this->user['game_data']['email']=$vars['email'];
		if (isset($vars['device_id']))
			$this->user['game_data']['device_id']=$vars['device_id'];
		if (isset($vars['server_id']))
			$this->user['game_data']['server_id']=$vars['server_id'];
		$this->__init_device();
		$this->__setup_password();
		$this->__init_post();
	}
	public function R_ReInit(){
		//reinit or clear current session on start
		$this->__reinit();
	}
	public function R_Remote($remote,$data=false){
		//do each remote request to the GAW server
		if (!isset($data))$data=array();
		if ($data==false)$data=array();
		if ((($remote != 'nmUser/tick')and ($remote != 'nmUser/getGameDataEx'))
			and((isset($this->user["remote"]["nmUser/getGameDataEx"]['response']["data"]))or(isset($this->user["remote"]["nmUser/getUserPlanetList"]['response']["data"])))
			and (isset($this->user["remote"]["nmItem/getItemPrice"]["response"]["data"])))
			$this->R_Ping();
		$common_data=$this->__common_data();
		$post=array();
		$sign="";
		$data=$this->__remote_pre_set($remote,$data);
		$reqtype=explode('/',$remote);
		switch($reqtype[0]){
			case "api_root":
				$urltype="root";
				if (!isset($data["pd"]))
					$data['pd']=array();
				$post="pd=".json_encode($data["pd"],JSON_FORCE_OBJECT);
				break;
			case "api_account":
				$urltype="spx";
				if (!isset($data["pd"]))
					$data['pd']=array();
				$post="pd=".json_encode($data["pd"],JSON_FORCE_OBJECT);
				break;
			case "":
				$urltype="";
				$url="http://".$this->hosts["spx"].$remote;
				break;
			case "nmLogin":
				$urltype="ing";
				if (!isset($data["data"]))
					$data['data']=array();
				$post="common_data=".json_encode($common_data)."&data=".json_encode($data["data"],JSON_FORCE_OBJECT);
				break;
			default:
				$urltype="ing";
				if (!isset($data["ex_data"]))
					$data['ex_data']=array();
				$post="user_id=".$this->user['game_data']["user_id"]."&user_name=".$this->user['game_data']["user_name"]."&common_data=".json_encode($common_data)."&ex_data=".json_encode($data['ex_data'],JSON_FORCE_OBJECT)."&type=1";
				$sign="?sign=".$this->__getSign($data['ex_data']);
				break;
		}
		if ($urltype!="")
			$url="http://".$this->hosts[$urltype].$this->root_url[$urltype]."/".$remote.$sign;
		$this->__open_url($url,$post);
		$this->__analyze_error();
		$this->__remote_post_set($remote);
	}
	public function R_Ping($time=30){
		return;
		if (strtotime("now")-strtotime($this->user["remote"]["nmUser/tick"]['update'])>$time){
			$this->R_Remote('nmUser/tick',array());
			if (
				(
					(isset($this->user["remote"]["nmUser/getGameDataEx"]['response']["data"]))
					or(isset($this->user["remote"]["nmUser/getUserPlanetList"]['response']["data"]))
				)
				and (isset($this->user["remote"]["nmItem/getItemPrice"]["response"]["data"]))
			){
				$data=array();
				if (isset($this->user["remote"]["nmUser/getGameDataEx"]["data"])){
					$data['planet_id']=
						$this->user["remote"]["nmUser/getGameDataEx"]["response"]["data"]["position"]["0"]."_".
						$this->user["remote"]["nmUser/getGameDataEx"]["response"]["data"]["position"]["1"]."_".
						$this->user["remote"]["nmUser/getGameDataEx"]["response"]["data"]["position"]["2"];
					$data['tick']=$this->user["remote"]["nmUser/getGameDataEx"]["response"]["data"]["chat"]["tick"];
				}
				$this->R_Remote('nmUser/getGameDataEx',$data);
				$this->R_gatherPresents();
			}
			else{
				echo "ERROR with pinging, please open nmItem/getItemPrice and nmUser/getUserPlanetList first\n";
			}
		}
	}
	public function R_Sleep($total_sleep){
		$sleep_sec=1;
		$tek_sleep=0;
		$step_sec=0;
		while (true){
			$this->R_Ping();
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
	public function R_gatherPresents(){
		//check if first request then update
		//Возможно тут может біть проблема, так как при взятии подарка он не пропадает из списка, возможно чтото поменялось в АПИ игры надо перепроверить, а также убедится нет ли глюка, так как несколько раз подряд взятия подарка не приводит к ошибке, надо убедится не присутсвует ли глюк позволяющий брать подарок несколько раз подряд.
		if ($this->config['auto_presents']!=true)
			return;
		if (isset($this->user['remote']['nmUser/getGameDataEx']['response']['data'])){
			if ($this->user['remote']['nmUser/getGameDataEx']['response']['data']['gift_state']>0){
				$this->R_Remote('nmActivity/getActivityList');
				if (isset($this->user['remote']['nmActivity/getActivityList']['response']['data']['activity'])){
					foreach ($this->user['remote']['nmActivity/getActivityList']['response']['data']['activity'] as $kk=>$val){
						if ($val['state']==1){
							echo "take present number: ".$val['text']['title']."\n";
							$this->R_Remote('nmActivity/getActivityReward',array("ex_data"=>array("activity_id"=>$val['activity_id'])));
							//if ($this->user['remote']['nmActivity/getActivityList']['response']['data']['activity'][$kk]['state']!=1)
								//echo Date("c")." WARNING: cannot take present ".$val['text']['title'].", with ID:".$va['activity_id']." skipping for now and for futher\n";
						}
					}
				}
			}
		}
	}
	public function R_Log($level,$text){
		// write to file and to output and to DB
		// file - logs\botname_username.log (DATE : botname : username : [level] text)
		// output - DATE : botname : username : [level] text
		// db - // TBD
		$debug=debug_backtrace();
		$func="main";
		if (isset($debug[1]['function'])){
			$func=$debug[1]['function'];
		}
		if ($func=='G_Log')
			$func="main";
		//$fa=explode("/",$f);
		//$file=end($fa);
		$str=Date("c")." [$func]";
		#$str=Date("c");
		switch ($level){
			case G_CALL:$str.=" CALL:";break;
			case G_INFO:$str.=" INFO:";break;
			case G_NOTICE:$str.=" NOTICE:";break;
			case G_WARNING:$str.=" WARNING:";break;
			case G_ERROR:$str.=" ERROR:";break;
			case G_FATAL:$str.=" FATAL:";break;
		}
		$str.=" $text\n";
		echo "$str";
	}
}
?>

