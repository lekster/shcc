<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
 
$ctl = new control_modules();

include_once(DIR_MODULES . 'zwave/zwave.class.php');

$zwave = new zwave();
$zwave->getConfig();

$tmp=SQLSelectOne("SELECT ID FROM zwave_devices LIMIT 1");
if (!$tmp['ID']) {
 exit; // no devices added -- no need to run this cycle
}

if (!$zwave->connect()) {
 echo "Cannot connect to Z-Wave API";
 exit; // cannot connect
}

while(1) 
{
   echo date("H:i:s") . " running " . basename(__FILE__) . "\n";
   setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time());


   // check all web vars
   $zwave->pollUpdates();

   if (file_exists('./reboot')) 
   {
      $db->Disconnect();
      exit;
   }

   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));

?>