<?php
include "../lib/gaw.php";
#$user=$argv[1];

#$user="Epifanov";
#$user="test1243";
#$user="Glory3d";
$user="btt10453671";
$user="Guaяdian";
$psw=$user;
$gaw=new GAW($user,true);
#$gaw->R_login();
#print_r($gaw->user);
#die();

$res=pg_query($gaw->db,"select user_name from all_users where error_code is null;");
$ares=pg_fetch_all($res);
$max=count($ares);
$x=0;
foreach($ares as $a){
	$u=str_replace("'","''",$a['user_name']);
	$gaw->Init($u,true);
	$gaw->user['game_data']['psw_clear']=$a['user_name'];
	$gaw->R_login();
	if ($gaw->user['remote_last_results']['R_login']['data']['error_code']=='1011')
		pg_query($gaw->db,"update all_users set ulcheck=true where user_name='$u';");
	if ($gaw->user['remote_last_results']['R_login']['data']['error_code']=='1001')
		pg_query($gaw->db,"update all_users set ulcheck=true,login='$u' where user_name='$u';");
	if ($gaw->user['remote_last_results']['R_login']['data']['error_code']=='1002')
		pg_query($gaw->db,"update all_users set ulcheck=true where user_name='$u';");
	if ($gaw->user['remote_last_results']['R_login']['data']['error_code']=='1')
		pg_query($gaw->db,"update all_users set ulcheck=true,login='$u',password='$u' where user_name='$u';");
	pg_query($gaw->db,"update all_users set error_code=".$gaw->user['remote_last_results']['R_login']['data']['error_code']." where user_name='$u';");
	$x++;
	echo "done $x from $max";
}

#$gaw->_setup_password();
#$gaw->R_reg();
#$gaw->R_login();

#$gaw->R_auto_login();

#$gaw->R_getUserList();
#$gaw->R_createUser();
#$gaw->R_enterGame();
#$gaw->R_getUserData();
#$gaw->R_getUnitConfig();


#$gaw->R_createUser();
#$gaw->R_useItem("147",-1,$gaw->user['mother']);

#$gaw->R_getAllInfo();//6589415
#$gaw->R_getBackTime(0);//Показывает информацию по любому флоту 
#$gaw->R_getUniverse(1,-1); // -1 показывает, проверить можно ли телепортироваться сюда
#$gaw->R_finishUpgrade('226_223_14',1);//+1 показало текущий статус , -1 выдало Notice, лучше не использовать, чужую нельзя тоже ошибка
#$gaw->R_getSpacecraft('226_223_14');//чужую указывать нельзя
#$gaw->R_overLooker('226_22_1');//Под антишпик не сканит
#$gaw->R_getPlanetInfo('226_22_1');//По чужой планете не показывает инфу
#$gaw->R_getPlanetData('226_22_1');//По чужой планете не показывает инфу
#$gaw->R_getPlanetQuardInfo('226_22_1');//По чужой планете не показывает инфу
#$gaw->R_getGalaxyGroupInfo(1,1);
#$gaw->R_getRadarFleets('226_223_17');
#$gaw->R_setUserName("ElNat");
#$gaw->R_useItem(179,-999,$gaw->user['mother']);
#$gaw->R_getItemCountInfo();
/*
foreach ($gaw->user['remote_last_results']['R_getItemCountInfo']['data']['info'] as $id =>$v){
	#for ($i=1;$i<=$gaw->user['remote_last_results']['R_getItemCountInfo']['data']['info'][$box]['count'];$i++){
		echo "open $id present, count ".$v['count']." ";
		if ($v['count']>0){
			$gaw->R_useItem($id,$v['count'],$gaw->user['mother']);
		}else echo "\n";
	#}
}
*/

#$box_for_open=array(63,65,67,147,179,185,179);
#$dx=file_get_contents("list.txt");
#$dy=json_decode($dx,JSON_OBJECT_AS_ARRAY);
#foreach($dy as $dd){
#	echo "take id:$dd ";
#	$gaw->R_useItem($dd,-7777,$gaw->user['mother']);
#}
#$dx=file_get_contents("list_box.txt");
#$dy=json_decode($dx,JSON_OBJECT_AS_ARRAY);
#foreach($dy as $dd){
#	echo "take box:$dd";
#	$gaw->R_useItem($dd,-999,$gaw->user['mother']);
#}
#$dx=file_get_contents("list_act.txt");
#$dy=json_decode($dx,JSON_OBJECT_AS_ARRAY);
#foreach($dy as $dd){
#	echo "activate id:$dd ";
#	$gaw->R_useItem($dd,7777,$gaw->user['mother']);
#}
#$dx=file_get_contents("list_box.txt");
#$dy=json_decode($dx,JSON_OBJECT_AS_ARRAY);
#foreach($dy as $dd){
#	echo "activate id:$dd ";
#	for ($i=1;$i<=2099;$i++){
#		$gaw->R_useItem(185,1,$gaw->user['mother']);
#		echo "$i\n";
#	}
#}

#$gaw->G_updatePlanetsInfo("all",0);
#print_r($gaw->user);
#$res=pg_query($gaw->db,"select acccount from accounts;");
#$resf=pg_fetch_all($res);
#$resf=array(array('acccount'=>'387119'));
#foreach($resf as $v){
#	echo $v['acccount']."\n";
#	$gaw->Init($v['acccount'],true);
#	$gaw->G_login();
#	foreach ($gaw->user['remote_last_results']['R_getUserList']['data']['users'] as $u){
#		pg_query("insert into users (user_id,user_name,level,score,acccount) values (".$u['user_id'].",'".$u['user_name']."',".$u['commander_info']['level'].",".$u['personal_score'].",'".$gaw->user['acccount']."') on conflict (user_name) do update set level=".$u['commander_info']['level'].",score=".$u['personal_score'].",acccount='".$gaw->user['acccount']."';");
#	}
#}
#die();
#$gaw->R_getAllInfo();
#$gaw->R_cancelFleet(14828454);
#$gaw->R_getUserPlanetList();
#$gaw->G_updatePlanetsInfo("all",0);
#$gaw->R_getPlanetData($gaw->user['mother']);
#$gaw->R_getSpacecraft($gaw->user['mother']);
#$gaw->R_getUniverse(171,380);
#$gaw->R_sentFleet(0,array("from"=>$gaw->user['mother'],"to"=>"172_380_2"),array(0=>0,1=>0,2=>0),array("2"=>1));
#$gaw->R_sentFleet(6,array("from"=>$gaw->user['mother'],"to"=>"110_517_9"),array(0=>0,1=>0,2=>0),array("2"=>1));
#$gaw->R_pushResBank($gaw->user['mother'],2,100000.11234);

#$gaw->R_pushResBank
#$gaw->R_overLooker($gaw->user['mother']);
#$gaw->R_overLooker("1_1_1");
#$gaw->R_getMailList();
#$gaw->R_getMailInfo(28097914,null);
#print_r($gaw->user);
#$gaw->G_Exit();
?>
