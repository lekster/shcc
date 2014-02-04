<?php
/*
* @version 0.2 (wizard)
*/

//var_dump($this->mode);die('asd');
//var_dump($rec);die();

  if ($this->mode=='setvalue') {
   global $prop_id;
   global $new_value;
   global $id;
   $this->setProperty($prop_id, $new_value);
   $this->redirect("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode);
  }

  if ($this->mode=='setvalue_action')
  {
    global $scheduled_job_action_id;
    global $new_value;
    global $id;
    global $scheduled_job_type_id;

    //check value
    $action = ScheduledJobActionAbstract::getActionByTypeId($scheduled_job_type_id);
    $ret = $action->check($new_value);
    if (!$ret)
    {
      $out['ERR_PropertyValue']=1;
    }
    else
    {
      $this->setScheduledJobActionValue($scheduled_job_action_id, $new_value);
      //var_dump("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode);die();
      $this->redirect("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode);
    }
    
  }

  if ($this->mode=='add_action')
  {
    global $scheduled_job_id;
    global $action_type_id;
    global $value;
    $action = ScheduledJobActionAbstract::getActionByTypeId($action_type_id);
    $ret = $action->check($value);
    if (!$action || !$ret)
    {
      $out['ERR_PropertyValue']=1;
    }
    else
    {
      $this->addScheduledJobAction($scheduled_job_id, $action_type_id, $value);
      //var_dump("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode);die();
      $this->redirect("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode);
    }
  }

  if ($this->mode=='delete_action')
  {
    global $scheduled_job_id;
    global $scheduled_job_action_id;
    $this->deleteScheduledJobAction($scheduled_job_id, $scheduled_job_action_id);
    //var_dump("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode);die();
    $this->redirect("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode);
  }

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='scheduled_job';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE scheduled_job_id='$id'");
  //var_dump($rec);var_dump($rec);  die();


  if ($this->mode=='update')
  {
       $ok=1;
      //updating 'HOSTNAME' (varchar)

       global $job_title;
       $rec['name']=$job_title;


       global $crontab;
       

       if ($rec['crontab'] != $crontab)
       {
          $rec['crontab']=$crontab;
          $rec['next_run_date'] = null;  
       } 

       global $is_active_cb;
       global $is_periodical_cb;
       $is_active = (isset($is_active_cb) && $is_active_cb == 1) ? 1 : 0;
       $is_periodical_cb = (isset($is_periodical_cb) && $is_periodical_cb == 1) ? 1 : 0;

       if ( ($rec['is_active'] == 0 && $is_active == 1) || ($rec['is_periodical'] == 0 && $is_periodical_cb == 1))
       {
          $rec['next_run_date'] = null;
       }

       $rec['is_active'] = $is_active;
       $rec['is_periodical'] = $is_periodical_cb;

       if (!$this->checkCrontabStr($crontab))
       {
          $ok = false;
          $out['ERR_Crontab']=1;
       }


      //UPDATING RECORD
       if ($ok) 
       {
   
          if ($rec['scheduled_job_id'])
          {
             SQLUpdate("scheduled_job", $rec, 'scheduled_job_id'); // update
          }
          else
          {
             $rec['scheduled_job_id']=SQLInsert($table_name, $rec); // adding new record
          }
          $out['OK']=1;
       }
       else
       {
          $out['ERR']=1;
       }
  }

  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
  $out['LOG']=nl2br(@$out['LOG']);

  if ($rec['scheduled_job_id']) 
  {

    $actions=SQLSelect("SELECT * FROM scheduled_job_action WHERE scheduled_job_id='".$rec['scheduled_job_id']."'");
    $result = array();
    foreach ($actions as $value)
    {
         $value['typeName'] = ScheduledJobActionAbstract::getTypeNameById($value["type_id"]);
         $result[] = $value;
         //$actions['typeInfo']
    }   
     $out['PROPERTIES']=$result;
  
  }

  //$out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
  $out['SCHEDULED_JOB_ACTIONS']=ScheduledJobActionAbstract::getInfoForWeb();
