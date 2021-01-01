<?php
include "../lib/gaw_raw.php";
$ONLY_ACTIVE=false;
$RAW_PRINT=true;

$iVars=array(
	"acccount"=>"387119",//only for login
	"psw_clear"=>"12e12E12e",//only for login
	"user_name"=>"ElNat",//require for enterGame
	"server_id"=>"163"//require
);
$gaw=new GAW_RAW();
$gaw->R_Init($iVars);

$iPoint="/spx_gsm/api_game_server/serverList/ING004/google/0201/0/ru";
$gaw->R_Remote($iPoint);
if ($RAW_PRINT==true){
	print_r($gaw->user);
	die("exit due to RAW_PRINT true");
}
foreach ($gaw->user["remote"][$iPoint]["response"]["data"]["server"] as $iServer){
	if ($ONLY_ACTIVE==true){
	if (($iServer["state"]==1)or($iServer["state"]==0)){
		echo $iServer["server_id"]." ".$iServer["name"]."\n";
	}
	}else{
		echo $iServer["server_id"]." ".$iServer["name"].", state: ".$iServer["state"]."\n";
	}
}
?>
