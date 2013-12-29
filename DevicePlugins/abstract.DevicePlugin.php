<?php

chdir(dirname(dirname(__FILE__)));

abstract class AbstractDevicePlugin
{
	protected static $pluginContainer = array();

    public static function LoadDevicePlugin($pluginName)
    {
        //die('asd1');
        if (isset(self::$pluginContainer[$pluginName]))
            return self::$pluginContainer[$pluginName];
        
            $basePath = './DevicePlugins/';
            $className = "DevicePlugin_" . $pluginName;
            $classFilePath = $basePath . "/$pluginName/class." . $pluginName . ".php";
            $ret = @include_once($classFilePath);
            if (!$ret)
            {
                //var_dump($ret);
                //$this->logger->error("Plugin FILE NOT FOUND", $classFilePath);
                return false;
            }
            //поискать в кеше
            $result = new $className();
            self::$pluginContainer[$pluginName] = $result;
            return $result;
    
    }

    public abstract function GetName();
    public abstract function GetVersion();
    public abstract function CheckState($device);
    public abstract function GetPortVal($device, $port);
    public abstract function SetPortVal($device, $port, $val);
    public abstract function GetPorts(); //return array ($port => $options,)
    public abstract function GetPortOptions($port);
    public abstract function IsAlive($device);

}


/*
From system

при апдейте статуса девайса, как и на какой объект.свойство апдейтить значение?

GetFacade()

drop table `device_plugin`

drop table `device_plugin`

CREATE TABLE `device_plugin` (                             
             `device_plugin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
         `name` varchar(255) NOT NULL DEFAULT '',
             `title` varchar(255) NOT NULL DEFAULT '',            
             PRIMARY KEY (`device_plugin_id`)                                   
           ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 


CREATE TABLE `devices` (                             
             `device_id` int(10) unsigned NOT NULL AUTO_INCREMENT,       
             `title` varchar(255) NOT NULL DEFAULT '',            
             `raw_id` varchar(255) NOT NULL,             
             `device_plugin_id` int NOT NULL ,
             `status` int(3) NOT NULL DEFAULT '0',                
             `check_latest` datetime DEFAULT NULL,                
             `check_next` datetime DEFAULT NULL,                  
             `script_id` int(10) NOT NULL DEFAULT '0',            
             `code` text,                                         
             `online_interval` int(10) NOT NULL DEFAULT '0',      
             `log` text,                                          
             PRIMARY KEY (`device_id`)                                   
           ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8  

#drop table `device_plugin_job`

CREATE TABLE `device_plugin_job` (                           
                `device_plugin_job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,        
                #`devei_plugin_id` int(10) unsigned NOT NULL,    
                `device_id` int(10) unsigned NOT NULL,             
                `command` varchar(255) NOT NULL,             
                `port` varchar(255) NOT NULL,
                `val` varchar(255) NOT NULL,              
                `run_at` datetime DEFAULT NULL,                      
                `ret_object_name` varchar(255) NOT NULL DEFAULT '',     
                `ret_object_property` varchar(255) NOT NULL DEFAULT '',   
                `start_execute_at` datetime DEFAULT NULL default NULL,              
                `executed_at` datetime DEFAULT NULL default NULL,
                `result` text DEFAULT NULL,
                `result_status` varchar(255) NOT NULL,      
                PRIMARY KEY (`device_plugin_job_id`)                                    
              ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 


*********************************************************************************

CREATE TABLE `device_plugin_properties` (
             `device_plugin_properties_id` int(10) unsigned NOT NULL AUTO_INCREMENT,                             
             `device_plugin_id` int(10) unsigned NOT NULL AUTO_INCREMENT,       
             `key` varchar(255) NOT NULL,
             `val` varchar(255) NOT NULL,             
             PRIMARY KEY (`device_plugin_properties_id`)                                   
           ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 

ALTER TABLE device_plugin_properties ADD CONSTRAINT uniq_id_key UNIQUE (device_plugin_properties_id, key);



CREATE TABLE `device_properties` (                           
                `property_id` int(10) unsigned NOT NULL AUTO_INCREMENT,        
                `device_id` int(10) unsigned NOT NULL DEFAULT '0',    
                `sysname` varchar(255) NOT NULL DEFAULT '',           
                `value` varchar(255) NOT NULL DEFAULT '',             
                `check_latest` datetime DEFAULT NULL,                 
                `updated` datetime DEFAULT NULL,                      
                `linked_object` varchar(255) NOT NULL DEFAULT '',     
                `linked_property` varchar(255) NOT NULL DEFAULT '',   
                `path` varchar(255) NOT NULL DEFAULT '',              
                PRIMARY KEY (`property_id`)                                    
              ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8  



 


$rec=array();
  $rec['plugin_id']=$pluginId;
  $rec['device_id']=$deviceId;
  $rec['command']=$command;
  $rec['port']=$port;
  $rec['val'] = $val;
  $rec['run_at']=date('Y-m-d H:i:s', $datetime);
 //$rec['EXPIRE']=date('Y-m-d H:i:s', $datetime+$expire);
  $rec['ret_object_name'] = $retObjectName;
  $rec['ret_object_property'] = $retObjectProperty;
  $rec['start_execute_at'] = null;
  $rec['executed_at'] = null;
  $rec['result'] = null;
  $rec['result_status'] = 'scheduled';


*/


/*
Plugin API For Device:

GetName()
GetVersion()
GetPortVal($device, $port)
SetPortVal($device, $port, $val)
GetPorts() return array ($port => $options,)
GetPortOptions($port)



Метод объекта в итоге выглядит так:

$plugin = LoadDevicePlugin('pluginName');
$ret = $plugin->SetPort(DEVICE_NAME, имя порта, значение);
$this->имя Свойства = $ret;


Либо через Job
//$plugin = LoadPlugin('pluginName');
AddDevicePluginJob('pluginName', 'deviceName', SetPort', имя порта, значение, $this->name, имя Свойства);
*/