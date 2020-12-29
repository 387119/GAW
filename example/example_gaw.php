<?php
include "../lib/gaw.php";
// Внимание для работы через этого бота нужнен доступ к базе.
$gaw=new GAW();
$gaw->G_Init("btt10453671");
//$gaw->G_Init("btt10453671","101");//явное указание сервера, иначе берёт по умолчанию из класса
$gaw->G_Login();
$gaw->G_updatePlanets();
$gaw->G_Sleep(10);

?>
