<?php
include "../lib/gaw.php";
$user=$argv[1];
$planetid=$argv[2];
$buildid=$argv[3];
$updown=$argv[4];//1 - up, 0 - down

#$user="ElNat";
#$user="Glory3d";
$gaw=new GAW($user);
$gaw->G_login();
/*
	Улучшение здания с использованием ресурсов из коробок
		Константы: 
			количетсво ресурсов в шмотках достаточное для того чтоб их активировать +10% запасу
			размер планеты достаточен для подьёма (если подьём)
			хранилища не заполненны на 100%
		Параметры: командир, планета, номер здания, 0/1 (повысить/понизить), 2_3_4 (количество коробок которое надо активировать), если 0_0_0 то ресурсов на планке достаточно для постройки
		Порядок:
			1 - активируем ресурсы (если надо)
			2 - запускаем постройку
			3 - используем ускорители (начиная с минимальных)
			
*/
// Проверки .... определяем сколько ресов надо для постройки запрошенного здания, определяем сколько ресов на планке, определяем заполненны ли хранилища полностью, определяем какие есть коробки (100к/1кк) так чтоб одного типа коробок біло достаточно для активации всех необходимых ресов
$gaw->R_getPlanetInfo($planetid);
$gaw->R_getItemCountInfo();//открываем шмотки
$items=array();
foreach ($gaw->user['remote_last_results']['R_getItemCountInfo']['data']['info'] as $v){
	$items[$v['id']]=$v['count'];
}
$speedup=array("1"=>300, "2"=>900, "3"=>1800, "4"=>3600, "5"=>10800, "6"=>28800, "7"=>54000, "8"=>86400);
$resbox=array("0"=>array("19"=>"100000","20"=>"1000000"),"1"=>array("22"=>"50000","23"=>"500000"),"2"=>array("25"=>"25000","26"=>"250000"));


$build_current=$gaw->user['remote_last_results']['R_getPlanetInfo']['data']['build'][$buildid]['lv'];
$build_do=$build_current-1+$updown;

$dres=pg_query($gaw->db,"select res0*(delta0^$build_do) as res0,res1*(delta1^$build_do) as res1,res2*(delta2^$build_do) as res2 from builds where build_id=$buildid;");
$res_need=pg_fetch_array($dres,NULL,PGSQL_NUM);
$res_now=$gaw->user['remote_last_results']['R_getPlanetInfo']['data']['res'];
print_r($res_need);
print_r($res_now);
$res_enought=true;
$boxes=array();
for ($i=0;$i<=2;$i++){
	if ($res_need[$i]>$res_now[$i]['now']){
		if ($res_now[$i]['now']>=$res_now[$i]['max']){
			echo "not enought res$i and cannot add need:".intval($res_need[$i]).", now:".intval($res_now[$i]['now']).", max:".$res_now[$i]['max'].", planet:$planetid\n";
			die();
		}else{
			$rineed=intval($res_need[$i]-$res_now[$i]['now']);
			// Определить какие ящики с сколько открыть
			$f=false;
			foreach ($resbox[$i] as $k=>$v){
				$b=intval($rineed/$v)+1;
				if ($items[$k]>=$b){
					$boxes[$k]=$b;
					$f=true;
					break;
				}
			}
			if ($f==false){
				echo "not enought res$i and cannot activate boxes as boxes not enought, need:".intval($res_need[$i]).", now:".intval($res_now[$i]['now']).", max:".$res_now[$i]['max'].", planet:$planetid\n";
				die();
			}
		}
		
	}
}
if ($res_enought==false){
	// недостаточно ресов для проведения изменения, проверяем можно ли активировать коробки, если да то какие и сколько
	echo "not enought";
}
print_r($boxes);
//die();
// 1 - Активируем коробки
echo "Активируем ресы из шмоток \n";
foreach($boxes as $k=>$v){
	if ($v>0)
		$gaw->R_useItem($k,$v,$planetid);
	
}
// 2 - Запускаем постройку
$time=0;
echo "изменяем уровень здания $buildid на планете $planetid";
if ($updown==1){
	$gaw->R_upgrade($planetid,$buildid);
	$time=$gaw->user['remote_last_results']['R_upgrade']['data']['time'];
}
if ($updown==0){
	$gaw->R_degrade($planetid,$buildid);
	$time=$gaw->user['remote_last_results']['R_degrade']['data']['time'];
}
// 3 - Используем ускорители
if ($time>10){
	echo "Используем ускорители на $time секунд\n";
	//R_useItem($id,$count,$planet,$buildid)	
	//id - Номер шмотки ускорителя
	//count - количество к использованию, надо проверить что можно активировать по нескольку ускорителей за 1 запрос
	// ускорители 1 - 5 мин, 2 - 15 мин, 3 - 30 мин, 4 - 1 час, 5 - 3 часа, 6 - 8 часов, 7 - 15 часов, 8 - 24 часа
	$x=1;
	while ($time>5){
		$count=intval($time/$speedup[$x])+1;
		if ($count>$items[$x])
			$count=$items[$x];
		if ($count>0){
			echo "Используем ускорители $count шт по ".$speedup[$x]." секунд, в сумме ".($count*$speedup[$x])."секунд\n";
			$gaw->R_useItem($x,$count,$planetid,$buildid);
			$time=$time-($count*$speedup[$x]);
		}else
			echo "Усорителей на ".$speedup[$x]." секунд ненайдено\n";
		$x++;
		if ($x==9)break;
	}
}
$gaw->G_ping(12);

if ($updown==1){
	$gaw->R_finishUpgrade($planetid,$buildid);
}
if ($updown==0){
	$gaw->R_finishDegrade($planetid,$buildid);
}
// Проверка что задача выполнена успешно
$gaw->G_ping(5);
$gaw->G_updatePlanetsInfo(array($planetid),0);
$build_new=$gaw->user['planets'][$planetid]['info']['data']['build'][$buildid]['lv'];
echo "Завершено: Здание $buildid, было $build_current, стало $build_new\n";
?>

