<?php
/**
* This file is part of MajorDoMo system. More details at http://smartliving.ru/
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.1
*/


include_once("./lib/loader.php");
require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");

// start calculation of execution time
startMeasure('TOTAL'); 

include_once(DIR_MODULES."application.class.php");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 


include_once(DIR_MODULES.'backup/backup.class.php');

$b = new backup();
$b->create_backup();

echo "DONE";

// closing database connection
$db->Disconnect(); 

// end calculation of execution time
endMeasure('TOTAL'); 

// print performance report
performanceReport(); 

?>