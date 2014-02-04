<?php
/**
* Timer Cycle script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.4
*/
 
chdir(dirname(dirname(__FILE__)));
require_once ("libraries/common/Worker/CronWorker.php");
require_once 'libraries/common/Doctrine/Common/ClassLoader.php';

require_once ("class.Facade.php");




use Doctrine\Common\ClassLoader,
   Worker\CronWorker,
   Doctrine\ORM\Tools\Setup,
   Doctrine\ORM\EntityManager;
   
class CycleWork extends \Worker\CronWorker
{

   protected $dbConnection;
   protected $configEm;
   protected $requestEm;

   protected $configsArr = array();
   protected $dev;

   public function customBeforeDoWork($params)
   {
      $this->dev=new scheduled_job();
      set_time_limit(0);
   }

   public function work($params)
   {
      $this->dev->executeJobs();
      sleep(1);
   }

}


$facade = Majordomo_Facade::getInstance("./config/current/global.php");
include_once('modules/scheduled_job/scheduled_job.class.php');

$a = new CycleWork("./config/current/global.php");
$a->doWork(null);




?>