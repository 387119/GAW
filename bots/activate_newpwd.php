<?php
include "../lib/gaw.php";
$user=$argv[1];

$gaw=new GAW($user,true);
$gaw->G_login();
?>
