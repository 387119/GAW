<?php
include "../lib/gaw.php";
$user=$argv[1];
#$user="21540098118";//test for up builds from zero
#$user="21540321118";//test for up builds from zero 2
#$timewait=900;// сколько ждать окончания постройки в онлайн - в идеале, с периодическим перезаходом
$timewait=1200;// 20 мин ожидать, специально для того чтоб первый подарок успевал браться

// пока строим только 0,1,2,3,7,8,9
$robo=7;
$bst=array(//Определяем порядок построек/исследований
	/*
			0 - здания
			0_0 - номер здания
			0_0_0 - уровень здания (строим до указанного уровня)
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
	"0_3_1",
	"0_0_1",
	"0_1_1",
	"0_3_2",
	"0_3_3",
	"0_2_1",
	"0_0_2",
	"0_1_2",
	"0_0_3",
	"0_3_4",
	"0_3_5",
	"0_2_2",
	"0_0_4",
	"0_1_3",
	"0_7_1",
	"0_8_1",
	"0_3_6",
	"0_0_5",
	"0_0_6",
	"0_3_7",
	"0_1_4",
	"0_1_5",
	"0_3_8",
	"0_0_7",
	"0_0_8",
	"0_3_9",
	"0_9_1",
	"0_0_9",
	"0_0_10",
	"0_3_10",
	"0_1_6",
	"0_1_7",
	"0_2_5",
	"0_3_11",
	"0_0_11",
	"0_3_12",
	"0_1_8",
	"0_7_2",
	"0_3_13",
	"0_0_12",
	"0_8_2",
	"0_9_2",
	"0_0_13",
	"0_0_14",
	"0_1_9",
	"0_3_14",
	"0_1_10",
	"0_0_15",
	"0_1_11",
	"0_3_15",
	"0_1_12",
	"0_0_16",
	"0_7_3",
	"0_7_4",
	"0_8_3",
	"0_3_16",
	"0_1_13",
	"0_0_17",
	"0_3_17",
	"0_1_14",
	"0_0_18",
	"0_3_18",
	"0_1_15",
	"0_0_19",
	"0_7_5",
	"0_3_19",
	"0_0_20",
	"0_1_16",
	"0_7_6",
	"0_8_4",
	"0_7_7",
	"0_8_5",
	"0_3_20",
	"0_1_17",
	"0_1_18",
	"0_0_21",
	"0_3_21",
	"0_0_22",
	"0_1_19",
	"0_3_22",
	"0_1_20",
	"0_3_23",
	"0_0_23",
	"0_1_21",
	"0_7_8",
	"0_0_24",
	"0_1_22",
	"0_8_6",
	"0_8_7",
	"0_9_3",
	"0_6_1",//строим лабу
	//"1_5_3"//подымаем энергетику для реактора
	//"0_11_8",//строим реактор
	//"0_2_12"//добиваем уровень газовой
);
// ****************************************************
// FUNCTION
function remove_finished_build(){
	global $gaw,$bst;
	$gaw->G_updatePlanets(false,6);
	$b_total=0;
	$b_left=0;
	for ($bc=0;$bc<=17;$bc++){
	//foreach (array(0,1,2,3,7,8,9) as $bc){
		$btek=$gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'][$bc]['lv'];
		foreach ($bst as $k=>$kb){
			list($bt,$bu,$bl)=split('_',$kb);
			if ($bt=='0'){
				$b_total++;
				if(($bu==$bc)and($bl<=$btek)){
					$b_left++;
					unset($bst[$k]);
				}
			}
		}
	}
	echo "BUILDS, total:$b_total, left:$b_left\n";
}
function remove_finished_laba(){
	// TBD
}
function remove_finished_ships(){
	// TBD
}
function remove_finished(){
	remove_finished_build();
}
function get_slots(){
	// Определяем есть ли постройки в процессе
	global $gaw,$timewait;
	$ret=array(
		"0"=>array('id'=>-1,"time"=>0),
		0,0);
	$b_slots=0;
	$b_time=0;
	foreach ($gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'] as $bi=>$b){
		if ($b['state']>0){
			$b_slots+=1;
			if ($b['time']>$ret[0]['time']){
				$ret[0]=array("id"=>$bi);
				$ret[0]=array("time"=>$b['time']);
			}
		}
	}
	
//	if ($b_slots>0){
//		echo "Build still in progress\n";
//		die();
//	}
	return $ret;
}
function wait($type){
	//0,1,2 - здания/лаба/корабли
	global $gaw,$timewait;
	$slots=get_slots();
	if ($slots[$type]['time']>0){
		if ($slots[$type]['time']>$timewait){
			echo "max time for finish is ".$slots[$type]['time']." exiting..\n";
			die();
		}else{
			echo "waiting ".$slots[$type]['time']." for finish...\n";
			$gaw->G_Sleep($slots[$type]['time']);
			$vars=array("ex_data"=>array("planet_id"=>$gaw->user['mother'],"build_id"=>$slots[$type]['id']));
			$gaw->R_Remote('nmBuild/finishUpgrade',$vars);
		}
	}
}
function build($need_build){
	global $gaw,$timewait;
	wait(0);
	$start_build=-1;//не строить ничего
	$res_curr=array(
		"0"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][0]['now']),
		"1"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][1]['now']),
		"2"=>intval($gaw->user['planets'][$gaw->user['mother']]['info']['data']['res'][2]['now'])
	);
	$rlv=$gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'][$need_build]['lv'];
	$sql="select res0*(delta0^$rlv) as res0,res1*(delta1^$rlv) as res1,res2*(delta2^$rlv) as res2 from builds where build_id=$need_build;";
	$res=pg_query($gaw->db,$sql);
	$resf=pg_fetch_all($res);
	$res_need=$resf[0];
	if (($res_curr[0]>=$res_need['res0'])and($res_curr[1]>=$res_need['res1'])and($res_curr[2]>=$res_need['res2']))
		$start_build=$need_build;
	if ($start_build>-1){
		echo "start $start_build\n";
		$vars=array("ex_data"=>array("planet_id"=>$gaw->user['mother'],"build_id"=>$start_build));
		$gaw->R_Remote('nmBuild/upgrade',$vars);
		$time=$gaw->user['remote']['nmBuild/upgrade']['response']['data']['time'];
		echo "time for upgrade $start_build is $time seconds\n";
		if ($time<=$timewait){
			echo "waiting... $time seconds\n";
			$gaw->G_Sleep($time+10);
			$gaw->R_Remote('nmBuild/finishUpgrade',$vars);
		}else{
			echo "much time to wait, exiting\n";
			die();
		}
		return true;
	}
	return false;
}

// *****************************************************
// START
$gaw=new GAW();
$gaw->G_InitId($user);
$gaw->G_Login();
remove_finished();
if (count($bst)==0){
	pg_query($gaw->db,"insert into bots.bot9_status values (".$gaw->user['game_data']['user_id'].",true) on conflict (user_id) do update set finished=true;");
}
/*
	смотрим есть ли постройки требующие финализации, если да то финализируем
		// nmPlanet/getPlanetInfo.response.data.build.ud.state=1 - идёт постройка
		// найти бы флаг, но пока не нашёл.
	Определяем есть ли свбодные слоты для постройки на материнской (пока) планете
	Если да то определяем достаточно ли ресов на постройку робо и нужно ли её строить
		запускаем постройку робо, переходим на конечную проверку.
	Определяем следующую по порядку необходимую постройку и проверяем достаточно ли ресов
		запускаем постройку и переходим на конечную проверку
		иначе выходим
	конечная проверка, если время постройки не превышает 15 минут то ждём (15 минут специально чтоб взять первый подарок для новых акков), финализируем постройку и переходим на начало на новую постройку
		иначе выходим
*/
// пробуем построить робофабрику
$gaw->G_updatePlanets(false,2);
while ($gaw->user['planets'][$gaw->user['mother']]['info']['data']['build'][4]['lv']<$robo){
	echo "trying up robo...\n";
	build(4);
	$gaw->G_updatePlanets(false,2);
}
//Строим постройки
//
$gaw->G_updatePlanets(false,2);
foreach ($bst as $bd){
	list($btype,$bnum,$blv)=split('_',$bd);
	if ($btype==0){
		echo "trying up $bnum from $blv\n";
		if (!build($bnum)){
			echo "can not up build $bnum from $blv, maybe due to not enough res, exiting\n";
			break;
		}
	}
}
$gaw->G_Exit();
?>
