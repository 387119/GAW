<?php

# непомню но выглядит старым, как создание карты

include "../lib/gaw.php";
$gaw=new GAW();

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
function bot2($uid){
	global $gaw;
	$gaw->G_InitId($uid);
	$gaw->G_Login();
	$gaw->G_UpdatePlanets(false,2);
	$gaw->R_Remote('nmItem/getItemCountInfo');
	$items=array();
	foreach ($gaw->user['remote']['nmItem/getItemCountInfo']['response']['data']['info'] as $item){
		$items[$item['id']]=$item['count'];
	}
	pg_query($gaw->db,"insert into userinfo (user_id,item,last_update) values (".$gaw->user['game_data']['user_id'].",'".(json_encode($items,JSON_FORCE_OBJECT))."',now()) on conflict (user_id) do update set item='".(json_encode($items,JSON_FORCE_OBJECT))."',last_update=now();
		insert into bots.bot2_status (user_id) values (".$gaw->user['game_data']['user_id'].") on conflict (user_id) do nothing;");
	if ($gaw->user['remote']['nmUser/getGameDataEx']['response']['data']['gift_state']>0){
		$sql="update bots.bot2_status set gift_count=0,gift_tstamp=now() where user_id=".$gaw->user['game_data']['user_id']." and (date_part('day',gift_tstamp)!=date_part('day',now()) or gift_tstamp is null);
			update bots.bot2_status set gift_count=gift_count+1,gift_tstamp=now() where user_id=".$gaw->user['game_data']['user_id'].";";
		pg_query($gaw->db,$sql);
	}
//	$res=pg_query($gaw->db,"select gal,sys,max(last_update) as last_update from universe group by server_id,gal,sys order by last_update limit 1;");
//	$resf=pg_fetch_all($res);
	die();
}

if (isset($argv[1]))
	bot2($argv[1]);

/// MAIN
$max_proc=100;
$btypes="4,7,8,11,12,13,14,17";
echo "starting main process....\n";
echo "$max_proc parallel php process allowed (in general)\n";
while (true){
	//check how many users we can open
	$curr_proc=shell_exec ("ps ax | grep php | wc -l");
	$curr_proc=trim($curr_proc);
	echo "current used slots $curr_proc \n";
	if (is_numeric($curr_proc))
		$free_proc=$max_proc-$curr_proc;
	else
		$free_proc=$max_proc;
	if ($free_proc>0){
		echo "get new $free_proc users\n";
		$sql="select u1.user_id 
			from users as u1 
			left join bots.bot2_status as b1 on (u1.user_id=b1.user_id) 
			where 1=1 
				and u1.ban=false 
				and (b1.gift_count<3 or b1.gift_count is null) 
				and (now()-b1.gift_tstamp > interval '1 hour' or b1.gift_tstamp is null) 
				and u1.type in ($btypes)
			order by 
				b1.gift_tstamp desc nulls last,
				u1.gold desc nulls last limit $free_proc;";
		$res=pg_query($gaw->db,$sql);
		$resf=pg_fetch_all($res);
		foreach ($resf as $v){
			if (strlen($v['user_id'])>0){
				exec("php ./bot2_online.php ".$v['user_id']." > /dev/null 2>&1 &");
			}else{
				echo "userid in null\n";
			}
		}
	}
	sleep(1);
}
?>

