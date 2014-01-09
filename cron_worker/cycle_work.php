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
   protected $threads = null;


   public function initDBAL($params = null)
   {
      $classLoader = new ClassLoader('Doctrine', 'libraries/common/');
      $classLoader->register();
      $doctrineConfig = Setup::createAnnotationMetadataConfiguration(array("./dbEntity/"), true);
      $conn = $this->config->get('DoctrineDatasourceDb', 'Datasource');
      //var_dump($conn);die('123');
      $this->requestEm = EntityManager::create($conn, $doctrineConfig);
      //$this->configEm->getConnection()->getWrappedConnection()->setAttribute(\PDO::ATTR_TIMEOUT, 4);
      //$this->requestEm->getConnection()->getWrappedConnection()->setAttribute(\PDO::ATTR_TIMEOUT, 4);
   }


   public function customBeforeDoWork($params)
   {
      $this->initDBAL();
      $this->initThreads();
   }


   public function initThreads()
   {

      include_once("./lib/threads.php");

      set_time_limit(0);

      $connected = 0;
      while(!$connected) 
      {
         $connected = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
         sleep(5);
      }

      // connecting to database
      $db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 
       
      echo "CONNECTED TO DB\n";

      include_once(DIR_MODULES."control_modules/control_modules.class.php");

      $ctl = new control_modules();
      
      
      $this->runStartUpScripts();
      // 1 second sleep
      sleep(1); 

      // getting list of /scripts/cycle_*.php files to run each in separate thread
      
      $cycles = $this->getCycleScripttFilePath();
      //var_dump($cycles);

      $this->threads = $this->startCycleThreads($cycles);
      echo "ALL CYCLES STARTED\n"; 
   }

   protected function createBackup()
   {
      // BACKUP DATABASE AND FILES
      $old_mask = umask(0);
       
      $backupDir = $this->config->get("BackupDir", "Global");

      if (!is_dir($backupDir)) 
      {
         mkdir($backupDir, 0777);
      }

      $targetDir = $backupDir . '/' . date('Ymd');


      $isReadyToBackup = (!is_dir($targetDir) && mkdir($targetDir, 0777)) ? true : false;

      /*$isReadyToBackup = false;
      if (!is_dir($targetDir) && mkdir($targetDir, 0777)) 
      {
         $isReadyToBackup = true;
      }
      */

      if ($isReadyToBackup) 
      {
         echo "Backing up files to $targetDir....";
         exec("/usr/bin/mysqldump --user=" . DB_USER . " --password=" . DB_PASSWORD . " --no-create-db --add-drop-table --databases ". DB_NAME . ">" . $targetDir . "/" . DB_NAME . ".sql");
         
         copyTree('./cms',    $targetDir . '/cms',    1);
         copyTree('./texts',  $targetDir . '/texts',  1);
         copyTree('./sounds', $targetDir . '/sounds', 1);
         echo "OK\n";
      }

      umask($old_mask);   
   }   

    protected function checkDb()
    {
         // CHECK/REPAIR/OPTIMIZE TABLES                
         $tables = SQLSelect("SHOW TABLES FROM " . DB_NAME);
         $total = count($tables);
          
         for( $i = 0; $i < $total; $i++)
         {
            $table = $tables[$i]['Tables_in_' . DB_NAME];
           
            echo $table . ' ...';
           
            if ($result=mysql_query("SELECT * FROM ".$table." LIMIT 1")) 
            {
               echo "OK\n";
            }
            else 
            {
               echo " broken ... repair ...";
               SQLExec("REPAIR TABLE " . $table);
               echo "OK\n";
            }
         }
    }

    protected function clearOldDbValues()
    {
         $this->requestEm->getConnection()->executeUpdate("DELETE FROM events WHERE ADDED > NOW()");
         $this->requestEm->getConnection()->executeUpdate("DELETE FROM phistory WHERE ADDED > NOW()");
         $this->requestEm->getConnection()->executeUpdate("DELETE FROM history WHERE ADDED > NOW()");
         $this->requestEm->getConnection()->executeUpdate("DELETE FROM shouts WHERE ADDED > NOW()");
         $this->requestEm->getConnection()->executeUpdate("DELETE FROM jobs WHERE PROCESSED = 1");
         $this->requestEm->getConnection()->executeUpdate("DELETE FROM history WHERE (TO_DAYS(NOW()) - TO_DAYS(ADDED)) >= 5");
    }


   protected function runStartUpScripts()
   {
      echo "Running startup maintenance\n";

      /*include("./scripts/startup_maintenance.php");
      */
      $this->createBackup();
      $this->checkDb();
      $this->clearOldDbValues();

      getObject('ThisComputer')->raiseEvent("StartUp");
   }

   protected function getCycleScripttFilePath()
   {
      $cycles = array();
       
      if ($lib_dir = @opendir("./scripts")) 
      {
         while (($lib_file = readdir($lib_dir)) !== false) 
         {
            if ((preg_match("/^cycle_.+?\.php$/", $lib_file))) 
            {
               $cycles[]='./scripts/'.$lib_file;
            }
         }
         closedir($lib_dir);
      }
      return $cycles;
   }

   protected function startCycleThreads($cycles)
   {
      $threads = new Threads;

      if (substr(php_uname(), 0, 7) == "Windows") 
      {
         $threads->phpPath = '..\server\php\php.exe';
      }
      else 
      {
         $threads->phpPath = 'php';
      }

      foreach($cycles as $path) 
      {
         if (file_exists($path)) 
         {
            $this->logger->debug("Starting ".$path." ... ");
            echo "Starting ".$path." ... ";
         
            if ((preg_match("/_X/", $path))) 
            {
               //для начала убедимся, что мы в Линуксе. Иначе удаленный запуск этих скриптов не делаем
               if (substr(php_uname(), 0, 5) == "Linux") 
               {
                  $display = '101';
            
                  //Попробуем получить номер Дисплея из имени файла
                  if ((preg_match("/_X(.+)_/", $path,$displays))) 
                  {
                     if (count($displays)>1) 
                     {
                        $display = $displays[1];
                     }
                  }
            
                  //запускаем Линуксовый поцесс на дисплее, номер которого в имени файла после _X
                  $pipe_id = $threads->newXThread($path, $display); 
               }
            } 
            else 
            {
               $pipe_id = $threads->newThread($path);
            }
         
            $pipes[$pipe_id] = $path;
         
            echo "OK\n";
         }
      }

      return $threads;
   }


   public function customAfterDoWork($params)   
   {
      // closing database connection
      //$db->Disconnect(); 
      $this->stopWork();
   }

   public function work($params)
   {
      $result = $this->threads->iteration();
      if (!empty($result))  
      {  
         $this->logger->debug("RESULT", $result);
         echo $result."\r\n";
      }
      
      if ($result === false)
      {
         $this->logger->debug("RESULT === FALSE--EXIT!!!");
         return -1;
      }
   }
}

$facade = Majordomo_Facade::getInstance("./config/current/global.php");

$a = new CycleWork("./config/current/global.php");
$a->doWork(null);

?>