<?php

require_once dirname(dirname(__FILE__)) . "/abstract.DevicePlugin.php";
require_once "./libraries/common/Caller/class.Caller.php";


class DevicePlugin_plugin_example extends AbstractDevicePlugin
{

	public function GetName() {}
    public function GetVersion() {}
    public function CheckState($device) {}
    public function GetPortVal($device, $port) {}
    public function SetPortVal($device, $port, $val) 
    {
    	$caller = new Caller("http://192.168.1.120/");
    	$res = $caller->call();
    	$res = substr($res, 1, 40);
    	return $res;
    }
    public function GetPorts() {} //return array ($port => $options,)
    public function GetPortOptions($port) {}


}


//$ret = AbstractDevicePlugin::LoadDevicePlugin('plugin_example');
//var_dump($ret->SetPortVal(1,2,3));