<?php
/*
* @version 0.2 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  //$qry="1";
  // search filters


  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM devices WHERE 1=1 ORDER BY device_id asc");
  if (@$res[0]['device_id']) 
  {
   //paging($res, 50, $out); // search result paging
   colorizeArray($res);
   //var_dump($res);die();
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
   
  }

?>