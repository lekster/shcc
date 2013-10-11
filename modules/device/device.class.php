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
//
//
//check to make sure the file exists
if(!function_exists('bcadd'))  {
  if(file_exists("/opt/owfs/bin/bcadd.php"))  {
    require "/opt/owfs/bin/bcadd.php";
  } else if(file_exists(DIR_MODULES."device/bcadd.php"))  {
    require DIR_MODULES."device/bcadd.php";
  } else  {
    die("File 'bcadd.php' is not found.\n");
  }
}

//check to make sure the file exists
if(file_exists("/opt/owfs/bin/ownet.php"))  {
  require "/opt/owfs/bin/ownet.php";
} else if(file_exists(DIR_MODULES."device/ownet.php"))  {
  require DIR_MODULES."device/ownet.php";
} else {
  die("File 'ownet.php' is not found.\n");
}


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
 function search_device(&$out) {
  require(DIR_MODULES.$this->name.'/device_search.inc.php');
 }
 
 /**
* display edit/add
*
* @access public
*/
 function edit_display(&$out, $id) {
  require(DIR_MODULES.$this->name.'/display_edit.inc.php');
 }
 
/**
* device edit/add
*
* @access public
*/
 function edit_device(&$out, $id) {
  require(DIR_MODULES.$this->name.'/device_edit.inc.php');
 }
 
/**
* device delete display
*
* @access public
*/
 function delete_display($id) {
  SQLExec("DELETE FROM owdisplays WHERE ID='".$id."'");
 }
 
/**
* device delete record
*
* @access public
*/
 function delete_device($id) {
  $rec=SQLSelectOne("SELECT * FROM owdevices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM owproperties WHERE DEVICE_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM owdevices WHERE ID='".$rec['ID']."'");
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
 function updateDevices($force=0, $device_id=0) {
  $sql=1;
  if (!$force) {
   $sql.=" AND CHECK_NEXT<='".date('Y-m-d H:i:s')."'";
  }
  if ($device_id) {
   $sql.=" AND ID='".(int)$device_id."'";
  }
  $devices=SQLSelect("SELECT ID, TITLE FROM owdevices WHERE ".$sql." ORDER BY CHECK_NEXT");
  $total=count($devices);
  for($i=0;$i<$total;$i++) {
   echo "Checking device: ".$devices[$i]['TITLE']."\n";
   $this->updateDevice($devices[$i]['ID']);
  }
 }

 function initDisplays() {
  $displays=SQLSelect("SELECT UDID FROM owdisplays");
  $total=count($displays);
  $ow=new OWNet(ONEWIRE_SERVER);
  for($i=0;$i<$total;$i++) {
   $ow->set($displays[$i]['UDID']."/LCD_H/message", str_pad("Starting...", 40));
  }
 }
 
 function updateDisplays($force=0, $display_id=0) {
  $sql=1;
  if (!$force) {
   $sql.=" AND UPDATE_NEXT<='".time()."'";
  }
  if ($display_id) {
   $sql.=" AND ID='".(int)$device_id."'";
  }
  $displays=SQLSelect("SELECT ID, TITLE FROM owdisplays WHERE ".$sql." ORDER BY UPDATE_NEXT");
  $total=count($displays);
  for($i=0;$i<$total;$i++) {
   echo "Updating display: ".$displays[$i]['TITLE']."\n";
   $this->updateDisplay($displays[$i]['ID']);
  }
 }
 
/**
* Title
*
* Description
*
* @access public
*/
 function scanDevices() {
  if (!defined('ONEWIRE_SERVER')) {
   return 0;
  }
  $ow=new OWNet(ONEWIRE_SERVER);
  $tmp=$ow->get("/",OWNET_MSG_DIR,false);
  if (!$tmp) {
   return 0;
  }
  $devices=explode(',', $tmp);
  $total=count($devices);
  for($i=0;$i<$total;$i++) {

   if (
    $devices[$i]=='/alarm' ||
    $devices[$i]=='/structure' ||
    $devices[$i]=='/system' ||
    $devices[$i]=='/settings' ||
    $devices[$i]=='/uncached' ||
    $devices[$i]=='/simultaneous' ||
    $devices[$i]=='/statistics' ||
    preg_match('/bus\.\d+$/', $devices[$i]) ||
    0
   ) {
    continue;
   }
   $udid=preg_replace('/^\//', '', $devices[$i]);
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

  //$ow=new OWNet(ONEWIRE_SERVER);
  //$ow->set($property['PATH'],$value);

  if ($update_device)
  {
     $this->updateDevice($property['device_id']);
  }

 }


 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function updateStarred() {

   if (!defined('ONEWIRE_SERVER')) {
    return 0;
   }

   $ow=new OWNet(ONEWIRE_SERVER);

   $properties=SQLSelect("SELECT owproperties.*, owdevices.SCRIPT_ID, owdevices.CODE, owdevices.UDID FROM owproperties, owdevices WHERE owdevices.ID=owproperties.DEVICE_ID AND owproperties.STARRED=1 ORDER BY owproperties.UPDATED DESC");
   $total=count($properties);

   for($i=0;$i<$total;$i++) {
    $prec=$properties[$i];
    $old_value=$prec['VALUE'];
    $value=trim($ow->get($prec['PATH'],OWNET_MSG_READ,false));

    if (!$value) {
     $device='/'.$prec['UDID'];
     $tmp=$ow->get($device,OWNET_MSG_DIR,false);
     if (!is_null($tmp)) {
      continue;
     }
    }


    if (!is_null($value) && $value!=$old_value) {
     $prec['VALUE']=$value;
     $prec['UPDATED']=date('Y-m-d H:i:s');

     $script_id=$prec['SCRIPT_ID'];
     $code=$prec['CODE'];

     unset($prec['SCRIPT_ID']);
     unset($prec['CODE']);
     unset($prec['UDID']);
     SQLUpdate('owproperties', $prec);


     if ($prec['LINKED_OBJECT'] && $prec['LINKED_PROPERTY']) {
      sg($prec['LINKED_OBJECT'].'.'.$prec['LINKED_PROPERTY'], $prec['VALUE'], 1);
     }

     $changed_values=array();
     $changed_values[$prec['SYSNAME']]=array('OLD_VALUE'=>$old_value, 'VALUE'=>$prec['VALUE']);

     $params=$changed_values;
     if ($script_id) {
      runScript($script_id, $params);
     } elseif ($code) {
      eval($code);
     }
    }
   }
  }

/**
* Title
*
* Description
*
* @access public
*/
function updateDisplay($id) {
  if (!defined('ONEWIRE_SERVER')) {
   return 0;
  }

  $rec=SQLSelectOne("SELECT * FROM owdisplays WHERE ID='".$id."'");
  if (!$rec['ID']) {
   return 0;
  }

  $ow=new OWNet(ONEWIRE_SERVER);
  $device='/'.$rec['UDID'];

  $rec['UPDATE_LATEST']=time();
  $rec['UPDATE_NEXT']=time()+(int)$rec['UPDATE_INTERVAL'];
  
  $rec['VALUE']=str_replace("\r", '', $rec['VALUE']);
  $text = explode("\n", $rec['VALUE']);

 
  for ($i = 1; $i <= $rec['ROWS']; $i++) {
        $line = $i.",1:".$text[$i-1];
        $line = processTitle($line);
    $ow->set($device."/LCD_H/screenyx", str_pad($line, 40));
  }
  
  SQLUpdate('owdisplays', $rec);
}

function isAlive($rawDeviceId)
{

  return 1;
}


 function updateDevice($id)
 {

    //обновляем девайс и его св-ва, точнее получаем значения для всех св-в



  $rec=SQLSelectOne("SELECT * FROM devices WHERE device_id='".$id."'");
  if (!isset($rec['device_id']))
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

  }

  /*
      $deviceId = @$jb['device_id'];
    if (isset($deviceId) && !isset($devices[$deviceId]))
    {
      $device = SQLSelectOne("select * from devices where device_id = '". DBSafe($deviceId) . "'");
      $devices[$deviceId] = $device;
    } 
    $device = @$devices[$deviceId];


    $devicePluginId = @$device['device_plugin_id'];
    if (isset($devicePluginId) && !isset($plugins[$devicePluginId]))
    {
        $plugin = SQLSelectOne("select * from device_plugin where device_plugin_id = '". DBSafe($devicePluginId) . "'");
        $plugins[$devicePluginId] = $plugin;
    }
    $plugin = @$plugins[$devicePluginId];
      
    //var_dump($device);
    //var_dump($plugin);
    
    $ret = LoadDevicePlugin(@$plugin['name']);
    if (isset($device) && isset($plugin) && is_object($ret))
    {

        $jb['start_execute_at']=date('Y-m-d H:i:s');
        $jb['result_status'] = 'executing...';
        SQLUpdate('device_plugin_job', $jb, 'device_plugin_job_id');

        $resultStatus = 'done';
        switch($jb['command'])
        {
            case 'SetPortVal':
              $result = $ret->SetPortVal($device['raw_id'],$jb['port'],$jb['val']);
  

  */









  //$ow=new OWNet(ONEWIRE_SERVER);
  $device='/'.$rec['raw_id'];

  $rec['check_latest']=date('Y-m-d H:i:s');
  $rec['check_next']=date('Y-m-d H:i:s', time()+(int)$rec['online_interval']);

  $old_status=$rec['status'];
  $rec['status'] = $this->isAlive($device);
  
  SQLUpdate('devices', $rec, 'device_id');
  var_dump($rec);
  die();


   if ($rec['status']!=$old_status && ($rec['script_id'] || $rec['code'])) 
   {
      $params=array();
      $params['device']=$device;
      $params['status']=$rec['status'];
      $params['status_changed']=1;
      if ($rec['script_id']) 
      {
        runScript($rec['script_id'], $params);
      } 
      elseif ($rec['code']) 
      {
         eval($rec['code']);
      }
   }

   if (!$rec['status']) 
   {
      return 0;
   }

   $changed_values=array();
   $changed=0;
   $properties=explode(',', $tmp);
   $totalp=count($properties);
   for($ip=0;$ip<$totalp;$ip++) 
   {
      $sysname=str_replace($device.'/', '', $properties[$ip]);
      //echo $properties[$ip]." (".$sysname."): ";
      $prec=SQLSelectOne("SELECT * FROM device_properties WHERE DEVICE_ID='".$rec['ID']."' AND SYSNAME='".DBSafe($sysname)."'");
      if (!$prec['ID']) 
      {
         $prec['DEVICE_ID']=$rec['ID'];
         $prec['sysname']=$sysname;
         $prec['path']=$properties[$ip];
         $prec['property_id']=SQLInsert('owproperties', $prec, 'property_id');
      }
      $old_value=$prec['VALUE'];
      $value=trim($ow->get($properties[$ip],OWNET_MSG_READ,false));
      if (!is_null($value) && $old_value!=$value) 
      {
          //if (1) {
         // value updated
         $changed=1;
         $changed_values[$prec['sysname']]=array('OLD_VALUE'=>$old_value, 'value'=>$prec['value']);
         $prec['value']=$value;
         $prec['updated']=date('Y-m-d H:i:s');
         SQLUpdate('device_properties', $prec, 'property_id');
         //$rec['LOG']=date('Y-m-d H:i:s')." ".$prec['SYSNAME'].": ".$prec['VALUE']."\n".$rec['LOG'];
         SQLUpdate('devices', $rec, 'device_id');
         if ($prec['linked_object'] && $prec['linked_property']) 
         {
            //!!!!!!!!!! вот тут обновляется св-во глоб объекта
            //var_dump('set');
            $facade = Majordomo_Facade::getInstance();
            $facade->setPropertyToObjectByName($prec['linked_object'], $prec['linked_property'], $prec['value'], false);
            //sg($prec['LINKED_OBJECT'].'.'.$prec['LINKED_PROPERTY'], $prec['VALUE'], 1);
         }
      }
   }

   if ($changed) 
   {
      $params=$changed_values;
      $params['DEVICE']=$device;
      if ($rec['script_id']) 
      {
        runScript($rec['script_id'], $params);
      } elseif ($rec['code']) {
        eval($rec['code']);
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