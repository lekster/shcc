<?php

require_once dirname(dirname(__FILE__)) . "/abstract.DevicePlugin.php";
require_once "./libraries/common/Caller/class.Caller.php";


class DevicePlugin_plugin_example extends AbstractDevicePlugin
{

    protected $container = array();

	public function GetName() {}
    public function GetVersion() {}
    public function CheckState($device) {}
    public function GetPortVal($device, $port) 
    {
        //$device = IP адрес
        //http://192.168.1.44/sec/?pt=2&cmd=get
        $url = "http://$device/sec/pt=$port&cmd=get";
        //var_dump($url);
        /*
        if (isset($this->container[$device][$port]))
           return $this->container[$device][$port];

        return null;
        */
        
        $caller = new Caller($url);
        $res = $caller->call();
        //var_dump($res);
        switch ($res) 
        {
            case 'OFF':
                $result = 0;
                break;
            case 'ON':
                $result = 1;
                break;
            default:
                if (is_numeric($res))
                {
                    $result = $res;
                }
                else
                {
                    $result = null;
                }
                break;
        }
        //var_dump($result);
        //var_dump("--------------------------");
        return $result;   
        //return rand(0,10);
    }
    public function SetPortVal($deviceId, $port, $val) 
    {
        //$device = IP адрес
        /*if (!isset($this->container[$device]))
            $this->container[$device] = array();
        $this->container[$device][$port] = $val;

        return $val;

        $caller = new Caller("http://192.168.1.120/");
        $res = $caller->call();
        $res = substr($res, 1, 40);
        return $res;
        */
    	
        //http://192.168.0.14/sec/?cmd=3:150
        $url = "http://$deviceId/sec/?cmd=$port:$val";
        //var_dump($url);
        $caller = new Caller($url);
        $res = $caller->call();
        //var_dump($res);
        return ($res) ? $res : null;

    }
    public function GetPorts() {return array("1"=>'port', "2"=>'port', "3"=>'port', "4"=>'port', "5"=>'port',
                                            "6"=>'port', "7"=>'port', "8"=>'port', "9"=>'port', "10"=>'port',
                                            "11"=>'port', "12"=>'port',);} //return array ($port => $options,)

    public function GetPortOptions($port) {}

    function ping($host, $timeout = 1) 
    {
        $result = false;
        $res = exec ("ping $host -c3", &$out, &$ret);
        /*var_dump($res);
        var_dump($out);
        var_dump($ret);
        */
        return !$ret;
    }

    public function IsAlive($device)
    {
        return $this->ping($device);
    }


}


//$ret = AbstractDevicePlugin::LoadDevicePlugin('plugin_example');
//var_dump($ret->SetPortVal(1,2,3));