<?php

chdir(dirname(__FILE__).'/../');
require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");
set_time_limit(0);

include_once(DIR_MODULES."control_modules/control_modules.class.php");

$ctl = new control_modules();

$timerClass = SQLSelectOne("SELECT * FROM classes WHERE TITLE LIKE 'timer'");
$o_qry = 1;
 
if ($timerClass['SUB_LIST']!='') 
   $o_qry.=" AND (CLASS_ID IN (".$timerClass['SUB_LIST'].") OR CLASS_ID=".$timerClass['ID'].")";
else 
   $o_qry.=" AND 0";

$old_minute = date('i');
$old_hour   = date('h');
$old_date   = date('Y-m-d');

while(1) 
{
   echo date("H:i:s") . " running " . basename(__FILE__) . "\n";

   setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time());

   $m  = date('i');
   $h  = date('h');
   $dt = date('Y-m-d');
  
   if ($m!=$old_minute) 
   {
      echo "new minute\n";
      $objects = SQLSelect("SELECT ID, TITLE FROM objects WHERE $o_qry");
      $total   = count($objects);
   
      for($i=0;$i<$total;$i++) 
      {
         echo $objects[$i]['TITLE'] . "->onNewMinute\n";
         getObject($objects[$i]['TITLE'])->raiseEvent("onNewMinute");
         getObject($objects[$i]['TITLE'])->setProperty("time", date('Y-m-d H:i:s'));
      }
   
      $old_minute=$m;
   }
  
   if ($h!=$old_hour) 
   {
      echo "new hour\n";
      $old_hour = $h;
      $objects  = SQLSelect("SELECT ID, TITLE FROM objects WHERE $o_qry");
      $total    = count($objects);
      
      for($i = 0; $i < $total; $i++)
         getObject($objects[$i]['TITLE'])->raiseEvent("onNewHour");
   }
   
   if ($dt != $old_date) 
   {
      echo "new day\n";
      $old_date = $dt;
   }

   if (file_exists('./reboot')) 
   {
      $db->Disconnect();
      exit;
   }

   sleep(1);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));

?>