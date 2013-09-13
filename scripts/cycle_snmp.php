<?php

chdir(dirname(__FILE__) . '/../');
require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");
set_time_limit(0);

include_once(DIR_MODULES . "control_modules/control_modules.class.php");
 
$ctl = new control_modules();

include_once(DIR_MODULES . 'snmpdevices/snmpdevices.class.php');

$snmpdevices = new snmpdevices();

$tmp=SQLSelectOne("SELECT ID FROM snmpdevices LIMIT 1");
if (!$tmp['ID']) {
 exit; // no devices added -- no need to run this cycle
}

while(1) 
{
   echo date("H:i:s") . " running " . basename(__FILE__) . "\n";

   setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time());
   // check all web vars
   $snmpdevices->readAll(); 

   if (file_exists('./reboot')) 
   {
      $db->Disconnect();
      exit;
   }

   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));

?>