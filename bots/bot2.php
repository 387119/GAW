<?php
include "../lib/gaw.php";
$user=$argv[1];
#$user="1591532046";
$gaw=new GAW();
$gaw->G_InitId($user);
$gaw->G_Login();
$gaw->R_Remote('nmFleet/getAllInfo');
//$gaw->R_getFrientList();
$gaw->G_updatePlanets(false,3);
if (isset($gaw->user['remote']['nmFleet/getAllInfo']['response']['data']['fleet'])){
	foreach ($gaw->user['remote']['nmFleet/getAllInfo']['response']['data']['fleet'] as $fleet){
		if (($fleet['time']<400000)and ($fleet['purpose']==9)and ($fleet['target']['2']>=17)){
       	         	foreach ($gaw->user['savers'] as $saver){
       		                //$save_user_id=$gaw->_is_user_online($save_user);
        	      	 	        //if ($save_user_id>0)
				$ex_data=array("fleet_uid"=>$fleet['fleet_uid'],"target_user_id_array"=>array("0"=>$saver['user_id']));
				$gaw->R_Remote('nmFleet/applyUnion',array("ex_data"=>$ex_data));
			}
		}
	}
}
#print_r($gaw->user);
#$gaw->G_sleep(2000);
$gaw->G_Exit();
?>
