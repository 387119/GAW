<?php
include "../lib/gaw.php";
$user=$argv[1];
$timewait=1200;// 20 мин ожидать, специально для того чтоб первый подарок успевал браться 
	/*
			0 - здания
			0_0 - номер здания
			0_0_0 - уровень здания (строим до указанного уровня)
			0_0_0_0/1 - если 0 то пропустить и перейти дальшей если нехватает ресов на постройку, 1 - ждать пока появятся ресы и не продолжать дальше
			1 - исследования
			1_0 - номер исследования
			1_0_1 - уровень исследования (строим до указанного уровня)
			2 - корабли
			2_1 - номер корабля
			2_1_10 - колво к постройке
			2_1_10_X - X условие постройки
					-1 - строим в любом случае
					0 - догнать до указанного количества (если => то не строим)
					>0 - построить указанное количество только если на планке меньше чем указанно
	*/
// FUNCTION
function get_bst(){
	// updated
	global $gaw;
	$ret=array();
	$res=pg_query($gaw->db,"select step_id||'_'||step_value||case when blocker then '_1' else '_0' end as bst from bots.bot9_steps where user_typeid=(select type from vusers where user_id=".$gaw->user['game_data']['user_id']." limit 1) and step_typeid=0 order by priority;");
	$resf=pg_fetch_all($res);
	if (!is_array($resf))
		die("no steps found. exit");
	foreach ($resf as $f){
		$ret[]=$f['bst'];
	}
	return $ret;
}
function remove_finished(){
	//updated
	global $gaw,$bst;
	$gaw->G_updatePlanets(false,6);
	$b_done=0;
	for ($bc=0;$bc<=17;$bc++){
		$btek=$gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'][$bc]['lv'];
		foreach ($bst as $k=>$kb){
			list($bu,$bl,$xx)=explode('_',$kb);
			if(($bu==$bc)and($btek>=$bl)){
				$b_done++;
				unset($bst[$k]);
			}
		}
	}
	echo "BUILDS, done:$b_done\n";
	$x=array_values($bst);
	if (is_array($x)) if (count($x)>0){
		echo "current status position is: ".$x[0]."\n";
		list ($x1,$x2,$x3)=explode('_',$x[0]);
		echo "$x1 $x2";
		//pg_query($gaw->db,"update bots.bot9_status_builds as bs1 set current_status=bs2.priority from bots.bot9_steps as bs2 where bs1.user_id=".$gaw->user['game_data']['user_id']." and bs2.user_typeid=(select type from users where user_id=".$gaw->user['game_data']['user_id'].") and bs2.step_typeid=0 and bs2.step_id=$x1 and step_value=$x2;");
	}
}
function get_slots(){
	// updated
	global $gaw,$timewait;
	$gaw->G_updatePlanets(false,2);
	$ret=array('id'=>-1,"time"=>0);
	$b_slots=0;
	$b_time=0;
	foreach ($gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'] as $bi=>$b){
		if ($b['state']>0){
			if (($b['state']==1)and($b['time']==0)){
				$vars=array("ex_data"=>array("planet_id"=>$gaw->user['mother'],"build_id"=>$bi));
				$gaw->R_Remote('nmBuild/finishUpgrade',$vars);
			}else{
				$b_slots+=1;
				if ($b['time']>$ret['time']){
					$ret["id"]=$bi;
					$ret["time"]=$b['time'];
				}
			}
		}
	}
	if ($b_slots>0){
		echo "Build still in progress\n";
		die("exit");
	}
	return $ret;
}
function wait(){
	//updated
	global $gaw,$timewait;
	$slots=get_slots();
	if ($slots['time']>0){
		$slots['time']=build_speedup($slots['id'],$slots['time']);
		//$slots=get_slots();
	}
	if ($slots['time']>0){
		pg_query($gaw->db,"insert into bots.bot9_status_builds (user_id,fin,uplevel_finish,tstamp) values (".$gaw->user['game_data']['user_id'].",false,now()+interval '".$slots['time']." seconds',now()) on conflict (user_id) do update set uplevel_finish=now()+interval '".$slots['time']." seconds',tstamp=now();");
		if ($slots['time']>$timewait){
			echo "max time for finish is ".$slots['time']." exiting..\n";
			die("exit");
		}else{
			if (isset($slots['id'])){
				echo "waiting ".$slots['time']." for finish...\n";
				$vars=array("ex_data"=>array("planet_id"=>$gaw->user['mother'],"build_id"=>$slots['id']));//prepare for type=0 before sleep
				$gaw->G_Sleep($slots['time']);
				$gaw->R_Remote('nmBuild/finishUpgrade',$vars);
			}else{
				echo "id in slots not found, user ".$gaw->user['game_data']['user_id']."\n";
				print_r($slots);
			}
		}
	}
	remove_finished();
}
function uplevel($id){
	//updated
	global $gaw,$timewait;
	$start=-1;//не строить ничего
	$gaw->G_updatePlanets(false,2);
	$res_curr=array(
		"0"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][0]['now']),
		"1"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][1]['now']),
		"2"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][2]['now'])
	);
	// определяем текущий уровень
	if (!isset($gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'][$id]['lv'])){
		echo "cannot find current level for build id: $id\n";
		print_r($gaw->user);
		die("exit");
	}
	$rlv=$gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'][$id]['lv'];
	if (!is_numeric($rlv)){
		echo "undefined level for build id: $id\n";
		print_r($gaw->user['planets'][$gaw->user['mother']]['info']['data']['build']);
		die("exit");
	}
	$res_need=get_res_need($id,$rlv);
	if (($res_curr[0]>=$res_need['res0'])and($res_curr[1]>=$res_need['res1'])and($res_curr[2]>=$res_need['res2']))
		$start=1;
	if ($start==1){
		echo "start up level id $id\n";
		$vars=array("ex_data"=>array("planet_id"=>$gaw->user['mother'],"build_id"=>$id));//only buildings
		$gaw->R_Remote('nmBuild/upgrade',$vars);
		$time=$gaw->user['remote']['nmBuild/upgrade']['response']['data']['time'];
		$time=build_speedup($id,$time);
		if (!is_numeric($time))
			$time=0;
		echo "time for upgrade id $id from level $rlv is $time seconds\n";
		pg_query($gaw->db,"insert into bots.bot9_status_builds (user_id,fin,uplevel_finish,tstamp) values (".$gaw->user['game_data']['user_id'].",false,now()+interval '$time seconds',now()) on conflict (user_id) do update set uplevel_finish=now()+interval '$time seconds',tstamp=now();");
		if ($time<=$timewait){
			echo "waiting... $time seconds\n";
			$gaw->G_Sleep($time+10);
			$gaw->R_Remote('nmBuild/finishUpgrade',$vars);
		}else{
			echo "much time to wait, exiting\n";
			die("exit");
		}
		return true;
	}
	return false;
}
function get_res_need($id,$level){
	//updated
	global $gaw;
	$ret=array("res0"=>0,"res1"=>0,"res2"=>0);
	$sql="select r0*(d0^$level) as res0,r1*(d1^$level) as res1,r2*(d2^$level) as res2 from uplevel where typeid=0 and id=$id;";
	$res=pg_query($gaw->db,$sql);
	$resf=pg_fetch_all($res);
	$ret=$resf[0];
	return $ret;
}
function get_user_speed_items(){
	//updated
	global $gaw;
	$ret=array();
	$gaw->R_Remote('nmItem/getItemCountInfo');
	foreach ($gaw->user['remote']['nmItem/getItemCountInfo']['response']['data']['info'] as $v){
		if (($v['id']>=1)and($v['id']<=8)){
			$ret[$v['id']]=$v['count'];
		}
	}
	return $ret;
}
function build_speedup($id,$time){
	//updated
	global $gaw;
	$speedupids=array("1"=>300, "2"=>900, "3"=>1800, "4"=>3600, "5"=>10800, "6"=>28800, "7"=>54000, "8"=>86400);
	$user_items=get_user_speed_items();
	for ($i=8;$i>=1;$i--){
		if ($user_items[$i]>0){
			for ($j=1;$j<=$user_items[$i];$j++){
				if ($speedupids[$i]<$time){
					$vars=array();
					$vars['ex_data']=array("planet_id"=>$gaw->user['mother'],"count"=>1,"role_object"=>1,"id"=>$i,"ex_id"=>$id);
					$gaw->R_Remote("nmItem/useItem",$vars);
					$time-=$speedupids[$i];
				}else
					break;
			}
		}
	}
	return $time;
}
// START
//////// from HERE
$gaw=new GAW();
$gaw->G_InitId($user);
$bst=get_bst();
$gaw->G_Login();
$gaw->G_updatePlanets(false,2);
remove_finished();
if (count($bst)==0){
	pg_query($gaw->db,"insert into bots.bot9_status_builds (user_id,fin) values (".$gaw->user['game_data']['user_id'].",true) on conflict (user_id) do update set fin=true,tstamp=now();");
	die("exit");
}
//Строим постройки
//$gaw->G_updatePlanets(false,2);
while (true){
	echo "wait till build is finished\n";
	wait();
	$nbst=array_values($bst);
	$bd=array_shift($nbst);
	list($bnum,$blv,$ismandatory)=explode('_',$bd);
	if ((!is_numeric($bnum))or(!is_numeric($blv))or(!is_numeric($ismandatory))){
		echo "wrong src data\n";
		print_r($bst);
		print_r($bd);
		die("exit");
	}
	echo "trying build id $bnum to $blv\n";
	if (!uplevel($bnum)){
		pg_query($gaw->db,"insert into bots.bot9_status_builds (user_id,fin,uplevel_finish,tstamp) values (".$gaw->user['game_data']['user_id'].",false,now()+interval '10 hours',now()) on conflict (user_id) do update set uplevel_finish=now()+interval '10 hours',tstamp=now();");
		echo "can not up build id $bnum to $blv level regarding step, maybe due to not enough res\n";
		if ($ismandatory==1){
			echo "this step is mandatory, exiting\n";
			die("exit");
		}
	}
	remove_finished();
}
$gaw->G_Exit();
?>
