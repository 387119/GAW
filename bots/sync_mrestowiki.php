<?php
pg_connect ("host=localhost port=5432 dbname=gaw user=gaw password=gaw");
$sql="
select                                                                                                                                                          
        u1.user_name,                                                                                                                                           
        u1.level,                                                                                                                                               
        u1.score,                                                                                                                                               
        u1.owner,                                                                                                                                               
        b0.online,                                                                                                                                              
        (now()-b1.last_save)::time(0) as last_save,                                                                                                             
        (now()-b1w.last_list_update)::time(0) as last_update,                                                                                                   
        b1.res0/1000000 as res0,                                                                                                                                
        b1.res1/1000000 as res1,                                                                                                                                
        b1.res2/1000000 as res2,                                                                                                                                
        b1.resmax/1000000 as resmax,                                                                                                                            
        b1.comment as b1_comment,                                                                                                                               
        coalesce(b1w.planets_total,0) as planets_total,
        coalesce(b1w.planets_ready,0) as planets_ready,                                                                                                         
        coalesce(b4.status,0) as b4_status,                                                                                                                     
        b4.comment as b4_comment                                                                                                                                
from users as u1                                                                                                                                                
        left join bot0 as b0 on (u1.user_name=b0.user_name)                                                                                                     
        left join bot1 as b1 on (b1.user_name=u1.user_name)                                                                                                     
        left join bot1_work_raw as b1w on (u1.user_name=b1w.user_name)                                                                                          
        left join bot4 as b4 on (u1.user_name=b4.user_name)                                                                                                     
where u1.type=2 and owner != ''  order by owner,user_name ;
";
$res=pg_query($sql);
$resf=pg_fetch_all($res);
$head="^S^Имя^ур^Очки^Батрак^МКГВМ^%^Сбор^Коментарий^Рутинка^Планеты^\n";
#echo $head;
$file_pre="/var/lib/dokuwiki/data/pages/bots/bot1_users_";
$file="";
$owner_tek="";
foreach($resf as $v){
	extract($v,EXTR_OVERWRITE);
	if ($b1_comment=="")$b1_comment=" ";
	if ($owner != $owner_tek){
		if ($owner_tek!=""){
			$str="^ ^ ^ ^ ^ ^<color #B1BDBA>$tres0/$tres1/$tres2</color>/<color #17A87F>$trestot</color>/<color #259BD2>$tresmax</color>^ ^ ^ ^ ^ ^\n";
			fwrite($f1,$str);
			fclose($f1);
		}
		$owner_tek=$owner;
		$file=$file_pre.$owner.".txt";
		$f1=fopen($file,"w");
		fwrite($f1,$head);
		$tres0=0;
		$tres1=0;
		$tres2=0;
		$trestot=0;
		$tresmax=0;
	}
	if ($online=='t')$S='{{fa>circle?color=#33ee11}}';
		else $S='{{fa>circle?color=#cccccc}}';
	if ($resmax==0)
		$proc=0;
	else
		$proc=intval(($res0+$res1+$res2)*100/$resmax);
	if ($proc>90)
		$proc="<color red>$proc</color>";
	if ($res0=="")$res0=0;
	if ($res1=="")$res1=0;
	if ($res2=="")$res2=0;
	$restot=$res0+$res1+$res2;
	$tres0+=$res0;
	$tres1+=$res1;
	$tres2+=$res2;
	$trestot+=$restot;
	$tresmax+=$resmax;
	switch ($b4_status){
		case 0:
			$b4="<todo>-</todo>";break;
		case 1:
			$b4="<todo #bot4>-</todo>";break;
		case 2:
			$b4="Сбор";
			$b1_comment=$b4_comment;
			break;
	}
	$STR="|$S|$user_name|$level|".number_format($score)."|$last_save|<color #B1BDBA>$res0/$res1/$res2</color>/<color #17A87F>$restot</color>/<color #259BD2>$resmax</color>|$proc|$b4|$b1_comment|$last_update|$planets_ready из $planets_total|\n";
	$STR=str_replace('||','| |',$STR);
	fwrite($f1,$STR);
}
$str="^ ^ ^ ^ ^ ^<color #B1BDBA>$tres0/$tres1/$tres2</color>/<color #17A87F>$trestot</color>/<color #259BD2>$tresmax</color>^ ^ ^ ^ ^ ^\n";
fwrite($f1,$str);
fclose($f1);
?>

