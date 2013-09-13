<?php
/**
* Test script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.3
*/

require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");
include_once("./lib/loader.php");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 

// language selection by settings
if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php')) 
   include_once (ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php');

include_once (ROOT . 'languages/default.php');

if (defined('SETTINGS_SITE_TIMEZONE')) 
{
   ini_set('date.timezone', SETTINGS_SITE_TIMEZONE);
}

header ('Content-Type: text/html; charset=utf-8');

echo timeNow();

// closing database connection
$db->Disconnect(); 

?>