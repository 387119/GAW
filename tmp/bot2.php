<?php
include "../lib/gaw.php";
$user='ElNat';
#$user=$argv[1];
$gaw=new GAW($user);
#if ($gaw->user['online']==true)
#	die("user is online");
$gaw->G_login();
$planets=$gaw->user['remote_last_results']['R_getGameDataEx']['data']['planet_count'];
$laba=$gaw->user['remote_last_results']['R_getGameDataEx']['data']['tec_info']['11']['lv'];
$mmore=intval($laba/2)-$planets+1;
echo "planet_tech: $planets/$laba/$mmore\n";
$gaw->R_getAllInfo();
$gaw->R_getFrientList();
$gaw->G_updatePlanetsInfo("all",0);                                                                                                   
echo "planets: ".implode(',',array_keys($gaw->user['planets']))."\n";                                                                 
// temporary open boxes
$gaw->R_getItemCountInfo();
print_r($gaw->user);
die();
$box_for_open=array(63,65,67,147,179,185);
foreach($box_for_open as $box){
	if (isset($gaw->user['remote_last_results']['R_getItemCountInfo']['data']['info'][$box]['count'])){
		$count=$gaw->user['remote_last_results']['R_getItemCountInfo']['data']['info'][$box]['count'];
		if ($count>50)$count=50;
		for ($i=1;$i<=$count;$i++){
			echo "open box $box, count $i of ".$gaw->user['remote_last_results']['R_getItemCountInfo']['data']['info'][$box]['count']." ";
			$gaw->R_useItem($box,1,$gaw->user['mother']);
#			$gaw->R_useItem($box,$count,$gaw->user['mother']);
		}
	}
}
if (isset($gaw->user['remote_last_results']['R_getAllInfo']['data']['fleet'])){
	foreach ($gaw->user['remote_last_results']['R_getAllInfo']['data']['fleet'] as $fleet){
		if (($fleet['time']<400000)and ($fleet['purpose']==9)and ($fleet['target']['2']>=17)){
       	         	foreach ($gaw->user['savers_users'] as $save_user){
       		                $save_user_id=$gaw->_is_user_online($save_user);
        	      	 	        if ($save_user_id>0)
              		         	        $gaw->R_applyUnion($fleet['fleet_uid'],$save_user_id);
			}
		}
	}
}
#print_r($gaw->user);
#$gaw->G_sleep(2000);
$gaw->G_Exit();
?>
