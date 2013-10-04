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


   public function customBeforeDoWork($params)
   {

   }

   public function work($params)
   {
      set_time_limit(0);
      runDevicePluginJob();
      sleep(1);
   }

}


$facade = Majordomo_Facade::getInstance("./config/current/global.php");
$a = new CycleWork("./config/current/global.php");
$a->doWork(null);

//addDevicePluginJob('dev1', 'SetPortVal', 2, 3, 'ThisComputer', '123', date("Y-m-d H:i:s"));die();

//$ret = LoadDevicePlugin('plugin_example');
//var_dump($ret->SetPortVal(1,2,3));



?>