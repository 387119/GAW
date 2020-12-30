<?php
include "../lib/gaw.php";
function update_universe($g,$s){
	global $gaw;
	$gaw->R_Remote('nmUniverse/getUniverse',array("ex_data"=>array("planet_id"=>-1,"sid"=>$s,"language"=>"ru", "gid"=>$g)));
	$r=$gaw->user['remote']['nmUniverse/getUniverse']['response']['data']['planets'];
	for ($p=1;$p<=15;$p++){
		if ($r[$p]['type']==0){
			$anti_check='false';
			$shield='false';
			if ($r[$p]['is_look']==1)
				$anti_check='true';
			if ($r[$p]['is_extend']==1)
				$shield='true';
			$sql="insert into universe (server_id,gal,sys,pos,planet,user_name,user_id,all_name,score,vip_lv,commander_lv,last_update,anti_check,shield) values (".$gaw->user['game_data']['server_id'].",$g,$s,".$r[$p]['position'][2].",E'".$r[$p]['name']."',E'".$r[$p]['user_name']."',".$r[$p]['user_id'].",E'".$r[$p]['all_name']."',".$r[$p]['score'].",".$r[$p]['vip_lv'].",".$r[$p]['commander_lv'].",now(),$anti_check,$shield) on conflict (server_id,gal,sys,pos) do update set planet=E'".$r[$p]['name']."',user_name=E'".$r[$p]['user_name']."',user_id=".$r[$p]['user_id'].",all_name=E'".$r[$p]['all_name']."',score=".$r[$p]['score'].",vip_lv=".$r[$p]['vip_lv'].",commander_lv=".$r[$p]['commander_lv'].",last_update=now(),anti_check=$anti_check,shield=$shield;";
			pg_query($gaw->db,$sql);
		}else {
//			$sql=" from universe where server_id=".$gaw->user['game_data']['server_id']." and gal=$g and sys=$s and pos=".$r[$p]['position'][2].";";
			// create update, not delete
			//pg_query($gaw->db,$sql);
		}
	}
}
$gaw=new GAW();
while (true){
	// 7,8,11,12
	$btypes="7,8,11,12,13,14,17";
	#$btypes="14";
	$sql="select user_id from bots.bot9_status where 1=1
			--and (finished=true or uplevel_finish>now()+interval '1 hour')
			and (user_id in (select user_id from users where type in ($btypes) and (ban=false or enabled=true)))
			and ( 
				(date_part('day',gift_tstamp)!=date_part('day',now() - interval '15 hours ')) 
				or (
					date_part('day',gift_tstamp)=date_part('day',now())
					and now()-gift_tstamp > interval '1 hour'
					and gift_count<3
				)
				or (gift_tstamp is null)
			);";
	#$sql="select user_id from users where type in ($btypes) and enabled=true;";
	#$sql="select user_id from users where user_id in (select user_id from planets where position like '7_%') and type in (7,11,12) and ban=false;";
	$res=pg_query($gaw->db,$sql);
	$resf=pg_fetch_all($res);
	foreach ($resf as $v){
		echo "try get gifts on ".$v['user_id']."\n";
		$gaw->G_InitId($v['user_id']);
		$gaw->G_Login();
		$gaw->G_UpdatePlanets(false,2);
		$gaw->R_Remote('nmItem/getItemCountInfo');
		$items=array();
		foreach ($gaw->user['remote']['nmItem/getItemCountInfo']['response']['data']['info'] as $item){
			$items[$item['id']]=$item['count'];
		}
		pg_query($gaw->db,"insert into userinfo (user_id,item,last_update) values (".$gaw->user['game_data']['user_id'].",'".(json_encode($items,JSON_FORCE_OBJECT))."',now()) on conflict (user_id) do update set item='".(json_encode($items,JSON_FORCE_OBJECT))."',last_update=now();");
		if ($gaw->user['remote']['nmUser/getGameDataEx']['response']['data']['gift_state']>0){
			pg_query($gaw->db,"update bots.bot9_status set gift_count=0,gift_tstamp=now() where user_id=".$gaw->user['game_data']['user_id']." and (date_part('day',gift_tstamp)!=date_part('day',now()) or gift_tstamp is null);
				update bots.bot9_status set gift_count=gift_count+1,gift_tstamp=now() where user_id=".$gaw->user['game_data']['user_id'].";");
		}
		$res=pg_query($gaw->db,"select server_id,gal,sys,max(last_update) as last_update from universe group by server_id,gal,sys order by last_update limit 1;");
		$resf=pg_fetch_all($res);
		//update_universe($resf[0]['gal'],$resf[0]['sys']);
		//$gaw->G_Exit();
		sleep (1);
	}
	sleep (60);
}
?>

