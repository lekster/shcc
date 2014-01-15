<?php
/**
* device 
*
* device
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.1
*/


class device extends module {
/**
* device
*
* Module class constructor
*
* @access private
*/
function __construct() 
{
  $this->name="device";
  $this->title="<#LANG_MODULE_ONEWIRE#>";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  //$this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();

  if ($this->mobile) {
   $out['MOBILE']=1;
  }

  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='device' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_device') {
   $this->search_device($out);
  }

  if ($this->view_mode=='scan') {
   $this->scanDevices();
   $this->redirect("?");
  }

  if ($this->view_mode=='edit_device') {
   $this->edit_device($out, $this->id);
  }
  
  if ($this->view_mode=='edit_display') {
   $this->edit_display($out, $this->id);
  }
  
  if ($this->view_mode=='delete_device') {
   $this->delete_device($this->id);
   $this->redirect("?");
  }
  
  if ($this->view_mode=='delete_display') {
   $this->delete_display($this->id);
   $this->redirect("?");
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* device search
*
* @access public
*/
 function search_device(&$out) 
 {
  require(DIR_MODULES.$this->name.'/device_search.inc.php');
 }
 
/**
* device edit/add
*
* @access public
*/
 function edit_device(&$out, $id) 
 {
  require(DIR_MODULES.$this->name.'/device_edit.inc.php');
 }
 

/**
* device delete record
*
* @access public
*/
 function delete_device($id) 
 {
  //$rec=SQLSelectOne("SELECT * FROM owdevices WHERE ID='$id'");
  // some action for related tables
  //SQLExec("DELETE FROM owproperties WHERE DEVICE_ID='".$rec['ID']."'");
  //SQLExec("DELETE FROM owdevices WHERE ID='".$rec['ID']."'");
 }

/**
* Title
*
* Description
*
* @access public
*/

/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }

/**
* Title
*
* Description
*
* @access public
*/
 function updateDevices($force=0, $device_id=0) 
 {
  $sql=1;
  if (!$force) {
   $sql.=" AND check_next<='".date('Y-m-d H:i:s')."'";
  }
  if ($device_id) {
   $sql.=" AND device_id='".(int)$device_id."'";
  }
  
  $devices=SQLSelect("SELECT device_id, title FROM devices WHERE ".$sql." ORDER BY check_next");

  $total=count($devices);
  for($i=0;$i<$total;$i++) {
   echo "Checking device: ".$devices[$i]['title']."\n";
   $this->updateDevice($devices[$i]['device_id']);
  }
 }

 
/**
* Title
*
* Description
*
* @access public
*/
 function scanDevices() 
 {
  /*
   $rec=SQLSelectOne("SELECT * FROM owdevices WHERE UDID='".$udid."'");
   if (!$rec['ID']) {
    $rec['UDID']=$udid;
    $rec['TITLE']=$rec['UDID'];
    $rec['STATUS']=1;
    $rec['ONLINE_INTERVAL']=60*60;
    $rec['LOG']=date('Y-m-d H:i:s').' Added';
    $rec['ID']=SQLInsert('owdevices', $rec);
   }
   $this->updateDevice($rec['ID']);
  }
  */
 }


/**
* Title
*
* Description
*
* @access public
*/
 function setProperty($prop_id, $value, $update_device=1) 
 {
  /*
  var_dump($prop_id);
  var_dump($value);
  var_dump($update_device);
  die('123');
  */

  $property=SQLSelectOne("SELECT * FROM device_properties WHERE property_id='".$prop_id."'");
  if (!$property['property_id']) {
   return 0;
  }
  /****************/

    $device=SQLSelectOne("SELECT * FROM devices WHERE device_id='".$property['device_id']."'");
    if (!isset($device['device_id']))
    {
      return 0;
    }
    $devicePluginId = @$device['device_plugin_id'];
    $plugin = SQLSelectOne("select * from device_plugin where device_plugin_id = '". DBSafe($devicePluginId) . "'");
    if (!$plugin) return 0;
    $ret = LoadDevicePlugin(@$plugin['name']);
    $ret->SetPortVal($device['raw_id'], $property['sysname'], $value);

  if ($update_device)
  {
     $this->updateDevice($property['device_id']);
  }

 }


function isAlive($rawDeviceId)
{

  return 1;
}

function fetchPropertiesFromPlugin($deviceId)
{
  //обновляем девайс и его св-ва, точнее получаем значения для всех св-в
    $device=SQLSelectOne("SELECT * FROM devices WHERE device_id='".$deviceId."'");
    if (!isset($device['device_id']))
    {
      return 0;
    }

    $devicePluginId = @$device['device_plugin_id'];
    $plugin = SQLSelectOne("select * from device_plugin where device_plugin_id = '". DBSafe($devicePluginId) . "'");
    if (!$plugin) return 0;
    
    $ret = LoadDevicePlugin(@$plugin['name']);
    if (isset($device) && isset($plugin) && is_object($ret))
    {
        //а тут обновляем все св-ва
        //для начала получим их из плагина
        $propertiesArr = $ret->GetPorts();
        //var_dump($propertiesArr);die();
         $properties=array_keys($propertiesArr);
         $totalp=count($properties);
         for($ip=0;$ip<$totalp;$ip++) 
         {
            $portName = $properties[$ip];   
            $sysname = $portName;
            $prec=SQLSelectOne("SELECT * FROM device_properties WHERE device_id='".$device['device_id']."' AND sysname='".DBSafe($sysname)."'");
            if (!$prec['property_id']) 
            {
               $prec['device_id']=$device['device_id'];
               $prec['sysname']=$sysname;
               $prec['path']=$properties[$ip];
               $prec['property_id']=SQLInsert('device_properties', $prec, 'property_id');
            }
          }
    }


}


/*
    public abstract function GetName();
    public abstract function GetVersion();
    public abstract function CheckState($device);
    public abstract function GetPortVal($device, $port);
    public abstract function GetPorts(); //return array ($port => $options,)
*/

 function updateDevice($id)
 {
    $this->fetchPropertiesFromPlugin($id);
    //обновляем девайс и его св-ва, точнее получаем значения для всех св-в
    $device=SQLSelectOne("SELECT * FROM devices WHERE device_id='".$id."'");
    if (!isset($device['device_id']))
    {
      return 0;
    }
    $device['CHECK_LATEST']=date('Y-m-d H:i:s');
    $device['CHECK_NEXT']=date('Y-m-d H:i:s', time()+(int)$device['online_interval']);
    SQLUpdate('devices', $device, 'device_id');

    $devicePluginId = @$device['device_plugin_id'];
    $plugin = SQLSelectOne("select * from device_plugin where device_plugin_id = '". DBSafe($devicePluginId) . "'");
    if (!$plugin) return 0;
    $ret = LoadDevicePlugin(@$plugin['name']);
   
    $propertiesArr = SQLSelect("SELECT * FROM device_properties WHERE device_id='".$device['device_id']. "'");

    //если девайс упал или восстановился - это тоже изменение сосотояния, вопрос как передавать в скрипт changed инфо о том что именно поменялось

    $changed = false;
    $changed_values = array();
    if (isset($device) && isset($plugin) && is_object($ret))
    {
        $isAlive = $ret->isAlive($device['raw_id']);
        if ($isAlive == FALSE)
        {
            if ($device['status'] == 1)  
            {
              $changed = true;
              $changed_values['isAlive']=array('old_value'=>1, 'value'=>0);
            }
            $device['status'] = 0;
            SQLUpdate('devices', $device, 'device_id');
        } 
        else
        {
          if ($device['status'] == 0)  
          {
            $changed = true;
            $changed_values['isAlive']=array('old_value'=>0, 'value'=>1);
          }  

          $device['status'] = 1;
          SQLUpdate('devices', $device, 'device_id');
          //а тут обновляем все св-ва
          $totalp=count($propertiesArr);
          for($ip=0;$ip<$totalp;$ip++) 
          {
            $prec= $propertiesArr[$ip];
            
            $oldPropertyVal=@$prec['value'];
            $value = $ret->GetPortVal($device['raw_id'], $prec['sysname']);
            //var_dump($prec);
            if (!is_null($value)) 
            {
               if ($oldPropertyVal!=$value) 
               {
                  $changed = true;
                  $changed_values[$prec['sysname']]=array('old_value'=>$oldPropertyVal, 'value'=>$value);
               }
               $prec['value']=$value;
               $prec['updated']=date('Y-m-d H:i:s');
               SQLUpdate('device_properties', $prec, 'property_id');
               //$device['LOG']=date('Y-m-d H:i:s')." ".$prec['sysname'].": ".$prec['value']."\n".$device['LOG'];
               SQLUpdate('devices', $device, 'device_id');
               if ($prec['linked_object'] && $prec['linked_property']) 
               {
                  //die('up linked');
                  $facade = Majordomo_Facade::getInstance();
                  $facade->setPropertyToObjectByName($prec['linked_object'], $prec['linked_property'], $prec['value'], false);
               }
            }
            
          }
       }


      //$changed = true;
      if ($changed) 
      {
        //$params=$changed_values;
        $params['device']=$device;
        if (@$device['script_id']) 
        {

          runScript($device['script_id'], $params);
        } elseif (@$device['code']) {
          eval($device['code']);
        }
      }
    }
 }

/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS owdevices');
  SQLExec('DROP TABLE IF EXISTS owproperties');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall() {
/*
onewire - device
*/
  $data = <<<EOD
 owdevices: ID int(10) unsigned NOT NULL auto_increment
 owdevices: TITLE varchar(255) NOT NULL DEFAULT ''
 owdevices: UDID varchar(255) NOT NULL DEFAULT ''
 owdevices: STATUS int(3) NOT NULL DEFAULT '0'
 owdevices: CHECK_LATEST datetime
 owdevices: CHECK_NEXT datetime
 owdevices: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 owdevices: CODE text
 owdevices: ONLINE_INTERVAL int(10) NOT NULL DEFAULT '0'
 owdevices: LOG text

 owproperties: ID int(10) unsigned NOT NULL auto_increment
 owproperties: DEVICE_ID int(10) unsigned NOT NULL DEFAULT '0'
 owproperties: SYSNAME varchar(255) NOT NULL DEFAULT ''
 owproperties: PATH varchar(255) NOT NULL DEFAULT ''
 owproperties: VALUE varchar(255) NOT NULL DEFAULT ''
 owproperties: CHECK_LATEST datetime
 owproperties: UPDATED datetime
 owproperties: STARRED int(3) unsigned NOT NULL DEFAULT '0'
 owproperties: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 owproperties: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''

 owdisplays: ID int(10) unsigned NOT NULL auto_increment
 owdisplays: UDID int(10) unsigned NOT NULL DEFAULT '0'
 owdisplays: TITLE varchar(255) NOT NULL DEFAULT ''
 owdisplays: ROWS int(3) unsigned NOT NULL DEFAULT '0'
 owdisplays: COLS int(3) unsigned NOT NULL DEFAULT '0'
 owdisplays: UPDATE_INTERVAL int(10) unsigned NOT NULL DEFAULT '0'
 owdisplays: VALUE text
 owdisplays: UPDATE_LATEST int(10) unsigned NOT NULL DEFAULT '0'
 owdisplays: UPDATE_NEXT int(10) unsigned NOT NULL DEFAULT '0'



EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDA2LCAyMDExIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>