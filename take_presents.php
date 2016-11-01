<?php
include "gaw_raw.php";
$user=$argv[1];
$check_anyway=$argv[2];
$status=exec ("./get_status.sh '${user}'");
if ($status!="offline")
        die(1);
$gaw=new GAW($user);
echo $gaw->user['device_id']."\n";
if ($check_anyway=="1"){
	$gaw->G_login();
	$gaw->G_Exit();

}
else{
	if (is_numeric($gaw->user["presents"])){
		if ($gaw->user["presents"]!=0){
			$gaw->G_login();
			$gaw->G_Exit();
		}
	}
	else{
		$gaw->G_login();
		$gaw->G_Exit();
	}
}
?>
