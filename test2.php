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

addDevicePluginJob('dev1124111114', 'SetProperty', 'p1', '7', '', '', null);

?>