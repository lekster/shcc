<?php

chdir(dirname(__FILE__).'/../');
require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");
set_time_limit(0);

include_once(DIR_MODULES."control_modules/control_modules.class.php");

$ctl = new control_modules();


if (@$_GET['once']) {
 setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time());
 cycleBody();
 echo "OK";
} else {
 while(1) {
   echo date("H:i:s") . " running " . basename(__FILE__) . "\n";
   setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time());
   cycleBody();
   if (file_exists('./reboot')) 
   {
      $db->Disconnect();
      exit;
   }
   sleep(1);
 }
 DebMes("Unexpected close of cycle: " . basename(__FILE__));
}

 function cycleBody() {
   // check main system states
   $objects = getObjectsByClass('systemStates');
   $total   = count($objects);
   for($i=0;$i<$total;$i++) 
   {
      $old_state = getGlobal($objects[$i]['TITLE'] . '.stateColor');
      callMethod($objects[$i]['TITLE'] . '.checkState');
      $new_state = getGlobal($objects[$i]['TITLE'] . '.stateColor');
  
      if ($new_state!=$old_state) 
      {
         echo $objects[$i]['TITLE'] . " state changed to " . $new_state . "\n";
         $params=array('STATE'=>$new_state);
         callMethod($objects[$i]['TITLE'] . '.stateChanged', $params);
      }
   }  
 }

?>