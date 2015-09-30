<?php

require_once 'pbr-lib-common/src/Zabbix/ZabbixApiAbstract.class.php';
require_once 'pbr-lib-common/src/Zabbix/ZabbixApi.class.php';

class Zabbix_Helper
{
	private $_zabbixApiUrl;
	//private $_zabbixLogin = "api_reader";
	//private $_zabbixPass = "api_reader";
	private $_zabbixLogin;
	private $_zabbixPass;
	private $_zabbixHost;

    const ITEM_TRAPPER_TYPE_FLOAT = 0;
    const ITEM_TRAPPER_TYPE_CHAR = 1;
    const ITEM_TRAPPER_TYPE_LOG = 2;
    const ITEM_TRAPPER_TYPE_INT = 3;
    const ITEM_TRAPPER_TYPE_TEXT = 4;


    const TRIGGER_SEVERITY_LOW = 0;
    const TRIGGER_SEVERITY_INFO = 1;
    const TRIGGER_SEVERITY_WARN = 2;
    const TRIGGER_SEVERITY_AVERAGE = 3;
    const TRIGGER_SEVERITY_HIGH = 4;
    const TRIGGER_SEVERITY_DISASTER = 5;

    public function __construct($zabbixHost, $login, $pass)
	{
		$this->_zabbixApiUrl = "http://" . $zabbixHost . "/zabbix/api_jsonrpc.php";
		$this->_zabbixLogin = $login;
		$this->_zabbixPass = $pass;
		$this->_zabbixHost = $zabbixHost;
	}

    //priority: 0 - not classified; 1 - information; 2 - warning; 3 - average; 4 - high; 5 - disaster.
    public function getTriggersInfoByPriority($priority){
        $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);

        $triggers = $api->triggerGet(array(
            'output' => 'extend',
            "selectFunctions" => "extend",
            "filter" => array(
                "priority" => $priority
            )
        ));

        if(count($triggers)>0){
            $result = array();
            foreach($triggers as $trigger){
                try {
                    $result [] = $this->getTrggerInfo($trigger->triggerid);
                }catch(Exception $e) {
                    echo $e->getMessage()."|".$trigger->triggerid;
                }
            }

            return $result;

        }else return array();
    }

    public function getTriggersInfoInproblemState()
    {
        $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);

        $triggers = $api->triggerGet(array(
            'output' => 'extend',
            "selectFunctions" => "extend",
            "filter" => array(
                "value" => 1,
            )
        ));

        if(count($triggers) > 0)
        {
            $result[] = array();
            foreach($triggers as $trigger)
            {
                try
                {
                    $result[] = $this->getTrggerInfo($trigger->triggerid);
                }
                catch(Exception $e)
                {
                    echo $e->getMessage(). "|" .$trigger->triggerid;
                }
            }
        }

        return isset($result) && is_array($result) ? $result : null;
    }
    public function getTriggersInfoByPriorityInProblemState($priority){
        $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);

        $triggers = $api->triggerGet(array(
            'output' => 'extend',
            "selectFunctions" => "extend",
            "filter" => array(
                "priority" => $priority,
                "value" => 1,
            )
        ));

        if(count($triggers)>0){
            $result = array();
            foreach($triggers as $trigger){
                try {
                    $result [] = $this->getTrggerInfo($trigger->triggerid);
                }catch(Exception $e) {
                    echo $e->getMessage()."|".$trigger->triggerid;
                }
            }

            return $result;

        }else return array();
    }

	public function getTrggerInfo($triggerId)
	{
		// connect to Zabbix API
	    $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);

	    $triggers = $api->triggerGet(array(
	        'output' => 'extend',
	        "triggerids"=> $triggerId,
	        "selectFunctions" => "extend"
	    ));
	    $result = array();

	    if (isset($triggers[0]))
	    {
	        $trigger = $triggers[0];
	        $result["id"] = $trigger->triggerid;
	        $result["status"] = $trigger->status;
	        $result["value"] = $trigger->value;
	        $result["description"] = $trigger->description;
	        $result["url"] = $trigger->url;

	        $events = $api->eventGet(array(
	            "output"=> "extend",
	            "select_acknowledges"=> "extend",
	            "triggerids"=> $triggerId,
	            "sortfield"=> "eventid",
	            "sortorder"=> "DESC"
	        ));

	        $lastEvent = @$events[0];
	        if (isset($lastEvent))
	        {
	            $ack = isset($lastEvent->acknowledges[0]->message) ? $lastEvent->acknowledges[0]->message : "N/A";

	            $result["eventId"] = $lastEvent->eventid;
	            $result["eventDate"] = date("Y-m-d H:i:s", $lastEvent->clock);
	            $result["isAcknowledged"] = $lastEvent->acknowledged;
	            $result["acknowledgeText"] = $ack;
	        }
		}
		else
		{
			return null;
		}
		return $result;

	}

	public function updateZabbixTriggerItem($triggerId, $description, $hostName, $itemKey, $expression1, $expression2, $url, $severity, $isActive)
	{
		$api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
		$req = array(
			"triggerid" => $triggerId,
			"description" => $description,
	        "expression" => "{" . $hostName . ":" . $itemKey . "." . $expression1 ."}" . $expression2,
	        "url" => $url,
	        "priority" => $severity,
	        "status" => $isActive ? 0 : 1,
	    );
	    //var_dump($req);
		$result = $api->triggerUpdate($req);
		//var_dump($result);
		return $result;
	}

	public function disableZabbixTriggerItem($triggerId)
	{
		$api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
		$req = array(
			"triggerid" => $triggerId,
	        "status" => 1,
	    );
	    //var_dump($req);
		$result = $api->triggerUpdate($req);
		//var_dump($result);
		return $result;
	}

	public function deleteZabbixTriggerItem($triggerId)
	{
		$api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
		$req = array(
			$triggerId
	    );
	    //var_dump($req);
		$result = $api->triggerDelete($req);
		//var_dump($result);
		return $result;
	}

	public function createZabbixTriggerItem($description, $hostName, $itemKey, $expression1, $expression2, $url, $severity, $isActive)
	{
		$api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
		$req = array(
			"description" => $description,
	        "expression" => "{" . $hostName . ":" . $itemKey . "." . $expression1 ."}" . $expression2,
	        "url" => $url,
	        "priority" => $severity,
	        "status" => $isActive ? 0 : 1,
	    );
	    //var_dump($req);
		$result = $api->triggerCreate($req);
		//var_dump($result);
		return $result;

	/*
	"method": "trigger.exists",
    "params": {
        "expression": "{Linux server:vfs.file.cksum[/etc/passwd].diff(0)}>0"
    },

    $expression = "$hostName:$itemKey.avg(300)}<3";


            $triggerId = $zabbixHelper->createTriggerId($name, $expression, $url, $severity, $isActive)

				method": "trigger.create",
                "params": {
                    "description": "Processor load is too high on {HOST.NAME}", Name of the trigger. 
                    "expression": "{Linux server:system.cpu.load[percpu,avg1].last(0)}>5",
                    url     string  URL associated with the trigger. 
                    priority    integer     Severity of the trigger.

                    Possible values are:
                    0 - (default) not classified;
                    1 - information;
                    2 - warning;
                    3 - average;
                    4 - high;
                    5 - disaster. 
                   
                    status  integer     Whether the trigger is enabled or disabled.

                    Possible values are:
                    0 - (default) enabled;
                    1 - disabled. 

                },


	*/

	}


	//stats.gauges.Statistic.Processings.Inplat.Total.Total.conversion_daily
	    //graphite
	public function isItemExists($hostName, $itemKey)
	{
		$api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);

	    $result = $api->itemExists(array(
	        "host" => $hostName,
        	"key_" => $itemKey,
	    ));

	    return $result;
		
	}

	public function getItem($hostName, $itemName)
	{

		$api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
		$req = array(
	        "output" => "extend",
	        "search" => array("key_" => $itemName),
	        "host" => $hostName,
            "sortfield" => "name",
	    );
	    //var_dump($req);
		$result = $api->itemGet($req);
		//var_dump($result);
		if (isset($result[0])) return $result[0];
		return null;


		/*
			"method": "item.get",
		    "params": {
		        "output": "extend",
		        "hostids": "10084",
		        "search": {
		            "key_": "system"
		        },
		        "sortfield": "name"
		    },

		*/
	}

    public function getAllItems($host)
    {
        $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
        $params = array(
            'host' => $host,
        );
        $result = $api->itemGetObjects($params);
        return isset($result) && is_array($result) ? $result : null;
        /*
            {
                "jsonrpc": "2.0",
                "method": "item.getobjects",
                "params": {
                    "host": "Zabbix server"
                },
                "auth": "3a57200802b24cda67c4e4010b50c065",
                "id": 1
            }
        */
    }

	public function isTriggerExists($hostName, $expression)
	{
        $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
        
	    $result = $api->triggerExists(array(
	        "expression" => $expression,
            "host" => $hostName,
	    ));

	    return $result;
		
	}


	public function getTriggersByDescription($hostName, $description)
	{

		$api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
		
		$hostName = "graphite";

		$result = $api->hostGet(array(
			"output" => "extend",
	        "filter" => array(
	            "host" => array($hostName)
            )
			));
		if (!isset($result[0])) return null;
		$hostId = $result[0]->hostid;

		$req = array(
	        'output' => 'extend',
	        "hostids"=> $hostId,
	        //"selectFunctions" => "extend",
	        //"select_functions" => "refer",
	        "filter" => array(
            	//"expression" => "{12981}>100",
            	"description" => $description,
            	//"description" => "Инплат конверсия общая (ч): низкий уровень",
        	),
	    );
	    //var_dump($req);
 		$result = $api->triggerGet($req);
		//var_dump($result);
 		return $result;
		
	}


	//$triggerIdArr = getTriggersByExpression()
            //getTriggersById
            
            //updateTrigger($triggerId, $params)
            //disableTriggerById()
            //enabletriggerById()
            //isTriggerExistsByExpression()


	/*
                "method": "trigger.exists",
                "params": {
                    "expression": "{Linux server:vfs.file.cksum[/etc/passwd].diff(0)}>0"
                },
            
                "method": "trigger.create",
                "params": {
                    "description": "Processor load is too high on {HOST.NAME}", Name of the trigger. 
                    "expression": "{Linux server:system.cpu.load[percpu,avg1].last(0)}>5",
                    url     string  URL associated with the trigger. 
                    priority    integer     Severity of the trigger.

                    Possible values are:
                    0 - (default) not classified;
                    1 - information;
                    2 - warning;
                    3 - average;
                    4 - high;
                    5 - disaster. 
                   
                    status  integer     Whether the trigger is enabled or disabled.

                    Possible values are:
                    0 - (default) enabled;
                    1 - disabled. 

                },
                
                "method": "trigger.get",
                "params": {
                    "output": [
                        "triggerid",
                        "description",
                        "priority"
                    ],
                    "filter": {
                        "value": 1
                    },
                    "sortfield": "priority",
                    "sortorder": "DESC"
                },


                "method": "trigger.update",
                "params": {
                    "triggerid": "13938",
                    "status": 0
                },
 


                $description": "Processor load is too high on {HOST.NAME}",
                $expression = "$hostName:$itemKey.avg(300)}<3";

            */
            

    public function getAllTriggers()
    {
        $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);

        $params = array(
            "output" => array(
                "triggerid",
                "name",
                "host",
                "description",
                "priority"
            ),
            "expandData" => true,
        );
        $result = $api->triggerGet($params);
        return isset($result) && is_array($result) ? $result : null;

    }


    public function isHostExists($host)
    {
        $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
        $params = array(
            "host" => $host,
        );
        $result = $api->hostExists($params);
        return $result;
    }

    public function getHostId($hostName)
    {
        $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
        $params = array(
            "output" => "extend",
            "filter" => array( 
                "host" => array(
                    $hostName
            ))
        );
        $result = $api->hostGet($params);
        if (is_object($result[0]))
            return $result[0]->hostid;
        return -1;
    }

    public function createHost($hostName)
    {
        $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
        $params = array(
            "host" => $hostName,
            "name" => $hostName,
            "groups" => array
            (
                "groupid" => "2"
            ),
            "interfaces" => array 
            (
                "type" => 1,
                "main" => 1,
                "useip" => 1,
                "ip" => "127.0.0.1",
                "dns" => "",
                "port" => "10050"
            )
        );
        $result = $api->hostCreate($params);
        if (is_object($result))
            return $result->hostids[0];
        return -1;

        /*
            "host": "Linux server",
        "interfaces": [
            {
                "type": 1,
                "main": 1,
                "useip": 1,
                "ip": "192.168.3.1",
                "dns": "",
                "port": "10050"
            }
        ],
        "groups": [
            {
                "groupid": "50"
            }
        ],
        "templates": [
            {
                "templateid": "20045"
            }
        ],
        "inventory": {
            "macaddress_a": "01234",
            "macaddress_b": "56768"
        }
        */

        /*
            HostCreateRequest request = new HostCreateRequest();
            HostCreateRequest.Params params = request.getParams();
            params.setHost(nodeName);
            params.setName(nodeName);
            params.addGroupId(2);
            
            // set host interface
            HostInterfaceObject hostInterface = new HostInterfaceObject();
            hostInterface.setIp("127.0.0.1");
            params.addHostInterfaceObject(hostInterface);
            HostCreateResponse response = zabbixApi.host().create(request);
            int ret = response.getResult().getHostids().get(0);
            this.zabbixHosts.put(nodeName, ret);
        */

    }

    public function createTrapperItem($hostId, $itemName, $itemKey, $type)
    {
        $api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);
        $result = $api->itemCreate(array(
            "name" => $itemName,
            "key_"=> $itemKey,
            "hostid"=> $hostId,
            "type"=> 2,
            "value_type"=> $type,
            "delay"=> 0,
            "history"=> "90",
            "trends"=> "365",
        ));

        return $result;
    }
    


	public function CreateZabbixTrapperItem($hostName, $itemKey, $itemName)
	{
		$api = new ZabbixApi($this->_zabbixApiUrl, $this->_zabbixLogin, $this->_zabbixPass);

		$hostId = null;
		$result = $api->hostGet(array(
	        'output' => 'extend',
	        'filter' => array('host' => array($hostName)),
	    ));

		 if (isset($result[0]))
	     {
	        $hostId = $result[0]->hostid;
	     }
	     else
	     {
	     	return null;
	     }

		////var_dump($hostId);



/*
$result = $api->itemGet(array(
	        'output' => 'extend',
	        "hostids" => $hostId,
	        'search' => array('key_' => "stats.gauges.Statistic.Processings.Inplat.Total.Total.conversion_daily"),
	    ));

var_dump($result);
*/

 $result = $api->itemCreate(array(
	        "name" => $itemName,
	        "key_"=> $itemKey,
	        "hostid"=> $hostId,
	        "type"=> 2,
	        "value_type"=> 0,
	        "delay"=> 0,
	        "history"=> "90",
    		"trends"=> "365",
	    ));

//var_dump($result);

/*

	    $result = $api->itemCreate(array(
	        "name": "uname",
	        "key_": "system.uname",
	        "hostid": "30021",
	        "type": 0,
	        "interfaceid": "30007",
	        "value_type": 1,
	        "delay": 10,
	        "inventory_link": 5

	    ));
	
	 ["itemid"]=>
    string(5) "23338"
    ["type"]=>
    string(1) "2"
    ["snmp_community"]=>
    string(0) ""
    ["snmp_oid"]=>
    string(0) ""
    ["hostid"]=>
    string(5) "10087"
    ["name"]=>
    string(47) "п≤пҐп©п╩п╟я┌ п╨п╬пҐп╡п╣я─я│п╦я▐ п╬п╠я┴п╟я▐ (пЄ)"
    ["key_"]=>
    string(70) "stats.gauges.Statistic.Processings.Inplat.Total.Total.conversion_daily"
    ["delay"]=>
    string(1) "0"
    ["history"]=>
    string(2) "90"
    ["trends"]=>
    string(3) "365"
    ["lastvalue"]=>
    string(1) "0"
    ["lastclock"]=>
    string(1) "0"
    ["prevvalue"]=>
    string(1) "0"
    ["status"]=>
    string(1) "0"
    ["value_type"]=>
    string(1) "0"
    ["trapper_hosts"]=>
    string(0) ""
    ["units"]=>
    string(0) ""
    ["multiplier"]=>
    string(1) "0"
    ["delta"]=>
    string(1) "0"
    ["prevorgvalue"]=>
    string(1) "0"
    ["snmpv3_securityname"]=>
    string(0) ""
    ["snmpv3_securitylevel"]=>
    string(1) "0"
    ["snmpv3_authpassphrase"]=>
    string(0) ""
    ["snmpv3_privpassphrase"]=>
    string(0) ""
    ["formula"]=>
    string(1) "1"
    ["error"]=>
    string(0) ""
    ["lastlogsize"]=>
    string(1) "0"
    ["logtimefmt"]=>
    string(0) ""
    ["templateid"]=>
    string(1) "0"
    ["valuemapid"]=>
    string(1) "0"
    ["delay_flex"]=>
    string(0) ""
    ["params"]=>
    string(0) ""
    ["ipmi_sensor"]=>
    string(0) ""
    ["data_type"]=>
    string(1) "0"
    ["authtype"]=>
    string(1) "0"
    ["username"]=>
    string(0) ""
    ["password"]=>
    string(0) ""
    ["publickey"]=>
    string(0) ""
    ["privatekey"]=>
    string(0) ""
    ["mtime"]=>
    string(1) "0"
    ["lastns"]=>
    string(1) "0"
    ["flags"]=>
    string(1) "0"
    ["filter"]=>
    string(0) ""
    ["interfaceid"]=>
    string(1) "0"
    ["port"]=>
    string(0) ""
    ["description"]=>
    string(0) ""
    ["inventory_link"]=>
    string(1) "0"
    ["lifetime"]=>
    string(2) "30"


*/
	    return $result;

	}

    public function sendTrapperItem($metricName, $val, $host = null)
    {
        $zabbixHost = $this->_zabbixHost;
        $zabbixSenderBin = file_exists('/usr/sbin/zabbix_sender') ?  '/usr/sbin/zabbix_sender' : '/usr/bin/zabbix_sender';
        ///usr/bin/zabbix_sender -z vps155.mtu.immo -p 10051 -s Module_MPInitPaymentResending -k x-death-rejected  -o 2
        ///usr/sbin/zabbix_sender [<Zabbix server> <port> <server> <key> <value>]
        $host = is_null($host) ? trim(`hostname`) : $host;
        $cmd1 = "$zabbixSenderBin $zabbixHost 10051 $host " . $metricName . " " . $val;
        $cmd2 = "$zabbixSenderBin -z $zabbixHost -p 10051 -s $host  -k $metricName -o \"$val\"";
        $command = file_exists('/usr/sbin/zabbix_sender') ? $cmd1 : $cmd2;
        exec($command, $op, $exitCode);
        if ($exitCode != 0)
        {
        	$command = !file_exists('/usr/sbin/zabbix_sender') ? $cmd1 : $cmd2;
        	exec($command, $op, $exitCode);
        }
    }
}