<?php
include "gaw_raw.php";
$user=$argv[1];
$check_anyway=$argv[2];
$status=exec ("./get_status.sh '${user}'");
if ($status!="offline")
        die(1);
$gaw=new GAW($user);
$do=false;
if ($check_anyway=="1")
	$do=true;
else{
	if (is_numeric($gaw->user["presents"])){
		if ($gaw->user["presents"]!=0)
			$do=true;
	}
	else
		$do=true;
}
if ($do==true){
	$gaw->G_login();
	$planets=$gaw->user['remote_last_results']['R_getGameDataEx']['data']['planet_count'];
	$laba=$gaw->user['remote_last_results']['R_getGameDataEx']['data']['tec_info']['11']['lv'];
	$mmore=intval($laba/2)-$planets+1;
	echo "planet_tech: $planets/$laba/$mmore\n";
	$gaw->R_getAllInfo();
	$gaw->R_getFrientList();
        $gaw->G_updatePlanetsInfo("all",0);                                                                                                   
        echo "planets: ".implode(',',array_keys($gaw->user['planets']))."\n";                                                                 

	foreach ($gaw->user['remote_last_results']['R_getAllInfo']['data']['fleet'] as $fleet){
		if (($fleet['time']<400000)and ($fleet['purpose']==9)and ($fleet['target']['2']>=17)){
	                foreach ($gaw->user['savers_users'] as $save_user){
        	                $save_user_id=$gaw->_is_user_online($save_user);
                	        if ($save_user_id>0)
                        	        $gaw->R_applyUnion($fleet['fleet_uid'],$save_user_id);
			}
		}
	}
	$gaw->G_Exit();
}
?>
