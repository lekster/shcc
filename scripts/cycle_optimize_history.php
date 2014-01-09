<?php

chdir(dirname(__FILE__).'/../');
require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");
set_time_limit(0);

 include_once(DIR_MODULES."control_modules/control_modules.class.php");
 $ctl = new control_modules();




function DeleteOldValues()
{
   $objects  = SQLSelect("select ph.ID
       from properties p
      join pvalues pv
      join phistory ph
      where p.ID = pv.PROPERTY_ID and p.OBJECT_ID=pv.OBJECT_ID
      and ph.VALUE_ID=pv.ID
      and UNIX_TIMESTAMP(ph.ADDED) <= UNIX_TIMESTAMP() - KEEP_HISTORY*86400");

      foreach ($objects as $val)
      {
        $id = $val['ID'];
        SQLExec("delete from phistory where ID = '$id'");
      }

   /*
   SQLExec("
      delete from phistory where
      ID in
      (
      #select KEEP_HISTORY, pv.*, ph.*,ph.ID as PH_ID,UNIX_TIMESTAMP(ph.ADDED),UNIX_TIMESTAMP() - KEEP_HISTORY*86400
      select ph.ID
       from properties p
      join pvalues pv
      join phistory ph
      where p.ID = pv.PROPERTY_ID and p.OBJECT_ID=pv.OBJECT_ID
      and ph.VALUE_ID=pv.ID
      and UNIX_TIMESTAMP(ph.ADDED) <= UNIX_TIMESTAMP() - KEEP_HISTORY*86400
      )
      ");
   */


}





while(1) 
{
   echo date("H:i:s") . " running " . basename(__FILE__) . "\n";

   DeleteOldValues();
   sleep(3600);
}

DebMes("Unexpected close of cycle: " . basename(__FILE__));

?>