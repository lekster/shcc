<?php
/**
* Write Error script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.1
*/

include_once("./lib/loader.php");
require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");

if ($error) 
{
   echo $error;
   DebMes("JAVASCRIPT Error: ".$error);
}

?>