<?php

chdir(dirname(__FILE__).'/../');
require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");
set_time_limit(0);

include_once(DIR_MODULES."control_modules/control_modules.class.php");

$ctl = new control_modules();

include_once(DIR_MODULES.'rss_channels/rss_channels.class.php');

$rss_ch = new rss_channels();

$checked_time=0;
while(1) 
{
   echo date("H:i:s") . " running " . basename(__FILE__) . "\n";

   if (time()-$checked_time>10) {
    $checked_time=time();   
    setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time());
    //updating RSS channels
    $to_update = SQLSelect("SELECT ID, TITLE FROM rss_channels WHERE NEXT_UPDATE <= NOW() LIMIT 1");
    $total = count($to_update);
    for($i=0;$i<$total;$i++) 
    {
      $rss_ch->updateChannel($to_update[$i]['ID']);
    }
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