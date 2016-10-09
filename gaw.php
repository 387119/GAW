<?php
include "gaw_raw.php";

//Initialization 
//$gaw=new GAW("login_name","login_id","user_name","user_id","pass_clear","pass_hash");

$gaw=new GAW("TEST_LOGIN","12341234","TEST_COMMANDER","12341234123","CLEAR_PASSWORD","PASSWORD_HASH");
$gaw->G_login();
$gaw->G_updatePlanetsInfo("all",0);//update info about planets 1 - array or string, array('12_12_12','14_14_5'), or string "all", second - after how many seconds need update data, 0 - now
#$gaw->R_getAllInfo();
#$gaw->R_getUserList();
#$test->R_getUniverse (10,10); // GALAXY,SYSTEM
$need['check_fly_fleets']=true;
while (true){
	$gaw->G_ping();// ping will be 1 time per 30 seconds
	$gaw->G_updatePlanetsInfo("all",120);//update info abount all planets each 120 seconds
	if ($need['check_fly_fleets']==true){
		$gaw->R_getAllInfo();//get info abount fleets
		$need['check_fly_fleets']=false;
	}
	break;
	sleep (2);
}
print_r ($gaw->user);
?>

