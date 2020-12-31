<?php
include "../lib/gaw_raw.php";
$vars=array(
	"acccount"=>"btt10453671",//only for login
	"psw_clear"=>"Qwdqwdqw1",//only for login
	"account_id"=>"21518698",//only for auto_login
	"password_hash"=>"41391D42DEC544E8232D24A703498088",//only for auto_login
	"user_name"=>"btt10453671",//require for enterGame
	"device_id"=>"9832370f-3d8a-43bc-9f5b-b09222fb2913",// not require at all, will generate automaticaly
	"server_id"=>"101"//require
);
$gaw=new GAW_RAW();
$gaw->R_Init($vars);
$data=array();
$gaw->R_Remote('api_account/auto_login',$data);//Автологин
$gaw->R_Remote('nmLogin/getUserList',$data);//Список командиров
$gaw->R_Remote('nmLogin/enterGame',$data);//Вход в игру под командиром
$gaw->R_Remote('nmUnit/getUnitConfig',$data);//обязательно
$gaw->R_Remote('nmItem/getItemPrice',$data);//Обязательно
$gaw->R_Remote('nmUser/getUserPlanetList',$data);//Список планет командира
$gaw->R_Remote('nmMail/getMailList',$data);//Список почты к примеру
$gaw->R_Sleep(120);//Спать 2 минуты в процессе при этом посылая обязательные пинги для поддержки сесии онлайн
print_r($gaw->user);
?>
