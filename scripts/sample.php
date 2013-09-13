<?php
/*
* @version 0.2 (auto-set)
*/


require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");
include_once("./lib/loader.php");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 
 
// closing database connection
$db->Disconnect(); 

?>