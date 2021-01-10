<?php
include dirname(__FILE__)."/../lib/gaw.php";
$gaw=new GAW();
print_r($gaw->cfgFile);
$iAccs=$gaw->dbQuery("select acccount from accounts;");
print_r($iAccs);
//foreach ($iAccs as $iAcc){
//	print $iAcc;
	//$gaw->G_Init("btt10453671");
	//$gaw->G_Init("btt10453671","101");//явное указание сервера, иначе берёт по умолчанию из класса
	//$gaw->G_Login();
	//$gaw->G_updatePlanets();
	//$gaw->G_Sleep(10);
//}

?>
