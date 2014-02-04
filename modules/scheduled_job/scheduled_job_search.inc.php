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
  $res=SQLSelect("SELECT * FROM scheduled_job WHERE 1=1 ORDER BY scheduled_job_id desc");
  if (@$res[0]['scheduled_job_id']) 
  {
   //paging($res, 50, $out); // search result paging
   //var_dump($res);
   colorizeArray($res);
   //var_dump($res);die();
   $out['RESULT']=$res;
   
  }
