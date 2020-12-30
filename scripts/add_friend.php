<?php
include "../lib/gaw.php";
$user=$argv[1];
$type=$argv[2];
$target=$argv[3];
$target_user=$target;
$gaw=new GAW();
$gaw->G_InitId($user);
$gaw->G_Login();

if ($type=='planet'){
	list($g,$s,$p)=explode('_',$target);
	$vars['ex_data']=array('sid'=>$s,"gid"=>$g);
	$gaw->R_Remote('nmUniverse/getUniverse',$vars);
	$target_user=$gaw->user['remote']['nmUniverse/getUniverse']['response']['data']['planets'][$p]['user_name'];
}
$vars['ex_data']=array('target_name'=>$target_user);
$gaw->R_Remote('nmFriend/addFriend',$vars);
?>
