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

   }

   protected function createBackup()
   {
      // BACKUP DATABASE AND FILES
      $old_mask = umask(0);
       
      if (!is_dir(DOC_ROOT . '/backup')) 
      {
         mkdir(DOC_ROOT . '/backup', 0777);
      }

      $target_dir  = DOC_ROOT . '/backup/' . date('Ymd');
      $full_backup = 0;

      if (!is_dir($target_dir)) 
      {
         mkdir($target_dir, 0777);
         $full_backup=1;
      }

      if ($full_backup) 
      {
         echo "Backing up files...";
         
         if (substr(php_uname(), 0, 7) == "Windows") 
         {
            exec(SERVER_ROOT . "/server/mysql/bin/mysqldump --user=root --no-create-db --add-drop-table --databases " . DB_NAME . ">" . $target_dir . "/" . DB_NAME . ".sql");
         }
         else 
         {
            exec("/usr/bin/mysqldump --user=" . DB_USER . " --password=" . DB_PASSWORD . " --no-create-db --add-drop-table --databases ". DB_NAME . ">" . $target_dir . "/" . DB_NAME . ".sql");
         }
        
         copyTree('./cms',    $target_dir . '/cms',    1);
         copyTree('./texts',  $target_dir . '/texts',  1);
         copyTree('./sounds', $target_dir . '/sounds', 1);
        
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
            DebMes("Starting ".$path." ... ");
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


   public function work($params)
   {
      //include_once("./config.php");
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

      $threads = $this->startCycleThreads($cycles);
      

      echo "ALL CYCLES STARTED\n";

      while (false !== ($result = $threads->iteration())) 
      {
         if (!empty($result))  echo $result."\r\n";
      }

      // closing database connection
      $db->Disconnect(); 
      $this->stopWork();

   }
}

$facade = Majordomo_Facade::getInstance("./config/current/global.php");

$a = new CycleWork("./config/current/global.php");
$a->doWork(null);

die('asd');

/*




include_once("./config.php");
include_once("./lib/loader.php");
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
 
include_once("./load_settings.php");

echo "CONNECTED TO DB\n";

include_once(DIR_MODULES."control_modules/control_modules.class.php");

$ctl = new control_modules();

echo "Running startup maintenance\n";
include("./scripts/startup_maintenance.php");

getObject('ThisComputer')->raiseEvent("StartUp");

// 1 second sleep
sleep(1); 

// getting list of /scripts/cycle_*.php files to run each in separate thread
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
      DebMes("Starting ".$path." ... ");
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

echo "ALL CYCLES STARTED\n";

while (false !== ($result = $threads->iteration())) 
{
   if (!empty($result))  echo $result."\r\n";
}

@unlink('./reboot');

// closing database connection
$db->Disconnect(); 
*/

?>