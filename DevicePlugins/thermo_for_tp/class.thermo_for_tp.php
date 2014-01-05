<?php

require_once dirname(__FILE__) . "/src/class.ThermoForTpConnector.php";


class DevicePlugin_thermo_for_tp extends AbstractDevicePlugin
{

    protected $container = array();

	public function GetName() {}
    public function GetVersion() {}
    public function CheckState($device) {}
    public function GetPortVal($device, $port) 
    {
        //$device = IP адрес
        //http://192.168.1.44/sec/?pt=2&cmd=get
        

        $a = new ThermoForTpConnector($device);
        //var_dump($device);
        //var_dump($port);
        $result = null;
        switch ($port)
        {
            case 'NeedTemp':
                $result = $a->GetNeedTemp();
                //var_dump($result);
                break;
            
            case 'RealTemp':
                $result = $a->GetRealTemp();
                break;    

            case 'RealAirTemp':
                $result = $a->GetRealAirTemp();
                break;        

            case 'WorkState':
                $result = $a->GetWorkState();
                break;

             case 'IP':
                $result = $a->GetIP();
               // var_dump($result);
                break;
            case 'MaxReleWorkTimeCount':
                $result = $a->GetMaxReleWorkTimeCount();
               // var_dump($result);
                break;    
            case 'TempGisteresis':
                $result = $a->GetTempGisteresis();
               // var_dump($result);
                break;        
            default:
                $result = 'N/A';
                break;
        }

        //die('asd');
        if ($result == null)
            $result = 'N/D';
        return $result;   
        //return rand(0,10);
    }
    public function SetPortVal($deviceId, $port, $val) 
    {
        //$device = IP адрес
        //var_dump($deviceId);
        //var_dump($port);
        //var_dump($val);
        
        $a = new ThermoForTpConnector($deviceId);
        switch ($port)
        {
            case 'NeedTemp':
                $result = $a->SetNeedTemp($val);
               // var_dump($result);
                break;

            case 'IP':
                $result = $a->SetIP($val);
               // var_dump($result);
                break;
            case 'MaxReleWorkTimeCount':
                $result = $a->SetMaxReleWorkTimeCount($val);
               // var_dump($result);
                break;    
            case 'TempGisteresis':
                $result = $a->SetTempGisteresis($val);
               // var_dump($result);
                break;    
            default:
                $result = null;
                break;
        }

        return $result;

    }
    public function GetPorts() 
    {
        return array("NeedTemp"=>'port', "RealTemp"=>'port', "RealAirTemp"=>'port', "WorkState"=>'port', "IP"=>'port', 
            'MaxReleWorkTimeCount' => 'port', 'TempGisteresis'=>'port');
    }

    public function GetPortOptions($port) {}

    function ping($host, $timeout = 1) 
    {
        /* ICMP ping packet with a pre-calculated checksum */
        $package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
        $socket  = @socket_create(AF_INET, SOCK_RAW, 1);
        @socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
        @socket_connect($socket, $host, null);
        $ts = microtime(true);
        @socket_send($socket, $package, strLen($package), 0);
        if (@socket_read($socket, 255)) {
            $result = microtime(true) - $ts;
        } else {
            $result = false;
        }
        @socket_close($socket);
        return 1;
        return $result;
    }

    public function IsAlive($device)
    {
        return $this->ping($device);
    }

}


//$ret = AbstractDevicePlugin::LoadDevicePlugin('plugin_example');
//var_dump($ret->SetPortVal(1,2,3));