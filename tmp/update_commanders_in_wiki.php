<?php
$path='/var/lib/dokuwiki/data/pages';
$blank='^S^Имя командира^Уровень^Очки^Тип^Владелец^';

include "gaw_raw.php";
if (isset($argv[1])){
	switch ($argv[1]){
		case "all":$updateall=true;break;
		case "new":$updateall=false;break;
		default: echo "usage php ./sctipt <all|new>\n";die();
	}
}
else {echo "usage php ./sctipt <all|new>\n";die();}

exec('cat '.$path.'/gaw_logins.txt | grep -vi "неправильный пароль" | grep "users:" | sed -e "s/.*:\(.*\)]].*/\1/"',$out);
foreach ($out as $user){
	$ufile=strtolower($path."/users/".$user.".txt");
	$needcheck=true;
	if ((file_exists($ufile))and($updateall==false))
		$needcheck=false;
	if ($needcheck==true){
		echo "getting info $user\n";
		$gaw=new GAW("$user",true);
		$gaw->G_login();
		//parce list and update wiki page
		#print_r($gaw->user);
		if (!file_exists($ufile)){
			file_put_contents($ufile,"===== Аккаунт ".$user." =====\n\n".$blank."\n");
			chown ($ufile,"www-data");
			chgrp ($ufile,"www-data");
		}
		$data_file=file($ufile);
		foreach($gaw->user['remote_last_results']['R_getUserList']['data']['users'] as $val){
			$filter=preg_grep("/".$val['user_name']."\|/",$data_file);
			if (count($filter)==0)
				$str="|{{fa>close?color=#cccccc}}|".
					$val['user_name']."|".
					$val['commander_info']['level']."|".
					number_format($val['personal_score'],0,',','.').
					"| | |\n";
				file_put_contents($ufile,$str,FILE_APPEND);
		}
		unset ($gaw);
	}
}
?>
