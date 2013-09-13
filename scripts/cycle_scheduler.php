<?php

chdir(dirname(__FILE__).'/../');
require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");
set_time_limit(0);

include_once(DIR_MODULES."control_modules/control_modules.class.php");

$ctl = new control_modules();
 
while(1) 
{
   echo date("H:i:s") . " running " . basename(__FILE__) . "\n";

   setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time());
   runScheduledJobs();

   if (file_exists('./reboot')) 
   {
      $db->Disconnect();
      exit;
   }

   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));

?>