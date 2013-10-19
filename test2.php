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

//addDevicePluginJob('dev1124111114', 'SetDeviceProperty', 'p1', '7', '', '', null);
//addDevicePluginJob('dev1124111114', 'SetPortVal', 'p1', '7', '', '', null);


$jb['port'] = 2;
$jb['val'] = 0;
$jb['command'] = 'SetDeviceProperty';


$deviceId = 1;
if (isset($deviceId))
{
	$device = SQLSelectOne("select * from devices where device_id = '". DBSafe($deviceId) . "'");
	$devices[$deviceId] = $device;
} 



$devicePluginId = @$device['device_plugin_id'];
if (isset($devicePluginId))
{
	$plugin = SQLSelectOne("select * from device_plugin where device_plugin_id = '". DBSafe($devicePluginId) . "'");
}

var_dump($device);
var_dump($plugin);


$ret = LoadDevicePlugin(@$plugin['name']);
if (isset($device) && isset($plugin) && is_object($ret))
{



	$resultStatus = 'done';
	switch($jb['command'])
	{
	    case 'SetPortVal':
	      $result = $ret->SetPortVal($device['raw_id'],$jb['port'],$jb['val']);
	    break; 

	    case 'SetDeviceProperty':
	      require_once('modules/device/device.class.php');
	      $dev=new device();
	      $propertyName = $jb['port'];

	      $property=SQLSelectOne("SELECT * FROM device_properties WHERE device_id='".$deviceId."'" . "and sysname='".DBSafe($propertyName)."'" );
	      var_dump($property);
	      if (!$property['property_id'])
	      {
	        $result = null;
	        $resultStatus = 'error';
	      }
	      else
	      {
	        $dev->setProperty($property['property_id'], $jb['val'], true); 
	        $result = null;
	      }
	    break;

	    default:
	       $result = null;
	       $resultStatus = 'error';
	}

}


?>