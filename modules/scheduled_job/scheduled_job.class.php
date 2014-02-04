<?php
/**
* device 
*
* device
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.1
*/

require_once dirname(__FILE__) . "/crontab.php";
require_once dirname(__FILE__) . "/Actions/ScheduledJobActionAbstract.php";

class scheduled_job extends module {
/**
* device
*
* Module class constructor
*
* @access private
*/
function __construct() 
{
  $this->name="scheduled_job";
  $this->title="scheduled job module";
  $this->module_category="<#LANG_SECTION_SYSTEM#>";
  //$this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run()
 {

  global $mode;
  if ($mode == 'getInfo')
  {
      $this->getInfo();
  }

  global $session;
  $out=array();

  if ($this->mobile) {
   $out['MOBILE']=1;
  }

  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  

  global $pd;
  $out['PD']=$pd;

  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }

  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out)
{
 
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source'])
 {
    $out['SET_DATASOURCE']=1;
 }

    if ($this->view_mode=='' || $this->view_mode=='search_scheduled_job')
    {
     $this->searchJob($out);
    }


    if ($this->view_mode=='edit_scheduled_job')
    {
     $this->editJob($out, $this->id);
    }
    
    if ($this->view_mode=='delete_scheduled_job')
    {
     $this->deleteJob($this->id);
     $this->redirect("?");
    }
    
  
  if ($this->mode=='add_new_job')
  {
      $this->addNewJob();
      $this->redirect("?");
      //var_dump("qwe");die();
  }

}


public function getInfo()
{
  global $scheduled_job_type_id;
  //var_dump($scheduled_job_type_id);
  //echo "test";die();
  if (is_numeric($scheduled_job_type_id))
  {
      $action = ScheduledJobActionAbstract::getActionByTypeId($scheduled_job_type_id);
      echo($action->getInfo());
  }
  else
  {
      echo "N/A";
  }
  exit(0);
}

/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* device search
*
* @access public
*/
 function searchJob(&$out) 
 {
  require(DIR_MODULES.$this->name.'/scheduled_job_search.inc.php');
 }
 
/**
* device edit/add
*
* @access public
*/
 function editJob(&$out, $id) 
 {
  require(DIR_MODULES.$this->name.'/scheduled_job_edit.inc.php');
 }
 

/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }



protected function updateNewTasks()
{
    $jobs=SQLSelect("SELECT scheduled_job_id, crontab, is_periodical FROM scheduled_job WHERE is_active = 1 and next_run_date is null");

    foreach ($jobs as $job)
    {
      $this->setNextRunTime($job);
    }
}

protected function setNextRunTime($job)
{
      $crontabStr = $job['crontab'];
      try
      {
        $nextRun = Crontab::parse($crontabStr);
        $job['next_run_date'] = date("Y-m-d H:i:s", $nextRun);
        var_dump($job['next_run_date']);
      }
      catch(Exception $e)
      {
        $job['status'] = -1;
      }
      SQLUpdate('scheduled_job', $job, 'scheduled_job_id');
}

protected function executeTasks()
{
    $jobs=SQLSelect("SELECT * FROM scheduled_job WHERE next_run_date <= now() and is_active = 1");

    foreach ($jobs as $job)
    {
      $actions = SQLSelect("SELECT * FROM scheduled_job_action WHERE scheduled_job_id = " . $job['scheduled_job_id']);
      $actionResult = true;
      foreach ($actions as $action)
      {
           $actionObj = ScheduledJobActionAbstract::getActionByTypeId($action['type_id']);
           $actionResult &= $actionObj->run($action['params']); 
      }

      if (!$actionResult)
      {
          $job['last_run_date'] = date("Y-m-d H:i:s");
          $job['next_run_date'] = null;
          $job['is_active'] = 0;
          $job['status'] = -1;
          SQLUpdate('scheduled_job', $job, 'scheduled_job_id');
      }
      else
      {
          $job['status'] = 0;
          $job['last_run_date'] = date("Y-m-d H:i:s");
          if ($job['is_periodical'] == 0 )
          {
              $job['next_run_date'] = null;
              $job['is_active'] = 0;
              SQLUpdate('scheduled_job', $job, 'scheduled_job_id');
          }
          else
          {
            //var_dump($job);
            //calculate next run
            $this->setNextRunTime($job);
          }
      }
      
      
    }
}

public function setScheduledJobActionValue($actionId, $val)
{
  $obj['params'] = $val;
  $obj['scheduled_job_action_id'] = $actionId;
  SQLUpdate('scheduled_job_action', $obj, 'scheduled_job_action_id');
}

public function addScheduledJobAction($scheduled_job_id, $action_type_id, $value)
{
    $obj['scheduled_job_id'] = $scheduled_job_id;
    $obj['type_id'] = $action_type_id;
    $obj['params'] = $value;
    SQLInsert('scheduled_job_action', $obj);
}

public function deleteScheduledJobAction($scheduledJobId, $scheduledJobActionId)
{
  SQLExec("DELETE FROM scheduled_job_action WHERE scheduled_job_action_id='".$scheduledJobActionId."'");
}

public function checkCrontabStr($crontab)
{
    return Crontab::checkCrontabStr($crontab);
}

public function addNewJob()
{
    $obj = array();            
    $obj['name'] = "NEW";
    $obj['crontab'] = "* * * * *";
    $obj['status'] = 0;
    $obj['is_active'] = 0;
    $obj['is_periodical'] = 0;
    SQLInsert('scheduled_job', $obj);
}

public function deleteJob($id)
{
  SQLExec("DELETE FROM scheduled_job_action WHERE scheduled_job_id='".$id."'");
  SQLExec("DELETE FROM scheduled_job WHERE scheduled_job_id='".$id."'");
}

/**
* Title
*
* Description
*
* @access public
*/
 function executeJobs() 
 {
    $this->updateNewTasks();
    $this->executeTasks();
 }

/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS scheduled_job');
  SQLExec('DROP TABLE IF EXISTS scheduled_job_action');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall() {

  $data = <<<EOD
 scheduled_job: scheduled_job_id int(10) unsigned NOT NULL auto_increment
 scheduled_job: name varchar(255) NOT NULL DEFAULT ''
 scheduled_job: crontab varchar(200) DEFAULT NULL,  
 scheduled_job: last_run_date datetime DEFAULT NULL,                          
 scheduled_job: next_run_date datetime DEFAULT NULL,                          
 scheduled_job: status int(11) NOT NULL DEFAULT '0',                          
 scheduled_job: is_active tinyint(1) NOT NULL DEFAULT '0',                    
 scheduled_job: is_periodical tinyint(1) NOT NULL DEFAULT '0',

 scheduled_job_action: scheduled_job_action_id int(10) unsigned NOT NULL AUTO_INCREMENT,  
 scheduled_job_action: type_id` int(11) NOT NULL,                                            
 scheduled_job_action: scheduled_job_id int(11) NOT NULL,                                    
 scheduled_job_action: params` varchar(200) NOT NULL
 
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
