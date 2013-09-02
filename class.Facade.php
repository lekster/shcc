<?php

require_once 'libraries/common/Doctrine/Common/ClassLoader.php';


use Doctrine\Common\ClassLoader,
   Worker\CronWorker,
   Doctrine\ORM\Tools\Setup,
   Doctrine\ORM\EntityManager;


require_once 'libraries/common/CronLock/class.CronLock.php';
require_once 'libraries/common/Config/class.Config.php';
require_once 'libraries/common/class.Exception.php';
include_once("lib/threads.php");

require_once 'DbEntity/SettingsEntity.php';

//require_once 'libraries/common/ServiceLocator/class.ServiceLocator.php';

/*
abstract class CronWorker
{

   protected $config = null;
   protected $logger = null;
   protected $cronLock = null;
   protected $isSigTerm = false;

   public function __construct($configName, $loggerIOCName = 'Logger', $cronlockIocName = 'CronLock')
   {
      
      $this->logger  = $this->config->getIOCObject($loggerIOCName);

*/
class Majordomo_Facade
{

	//protected $requestEm;
   //protected $configsArr = array();

   protected $config;
   protected $logger;
   protected $dbConnection;

   protected static $instance = null;

   protected function __construct($configFilePath)
   {
      $this->config = new \Immo_MobileCommerce_Config($configFilePath);
      $this->initDBAL();
      $this->configurate();
      $this->autoload(); 
      $this->loadSettings();  
      $this->loadLanguage();
   }

   public static function getInstance($configFileName = null)
   {
      if (!self::$instance)
         self::$instance = new self($configFileName);
      return self::$instance;

   }

   protected function initDBAL($params = null)
   {
      $classLoader = new ClassLoader('Doctrine', 'libraries/common/');
      $classLoader->register();
      $doctrineConfig = Setup::createAnnotationMetadataConfiguration(array("./DbEntity/"), true);
      $conn = $this->config->get('DoctrineDatasourceDb', 'Datasource');
      //var_dump($conn);die('123');
      $this->dbConnection = EntityManager::create($conn, $doctrineConfig);
      //$this->configEm->getConnection()->getWrappedConnection()->setAttribute(\PDO::ATTR_TIMEOUT, 4);
      //$this->requestEm->getConnection()->getWrappedConnection()->setAttribute(\PDO::ATTR_TIMEOUT, 4);
   }

   //get from config.php
   protected function configurate()
   {
       Define('DB_HOST', 'localhost');
       Define('DB_NAME', 'majordomo1');
       Define('DB_USER', 'root');
       Define('DB_PASSWORD', 'root');

       Define('DIR_TEMPLATES', "./templates/");
       Define('DIR_MODULES', "./modules/");
       Define('DEBUG_MODE', 1);
       Define('UPDATES_REPOSITORY_NAME', 'smarthome');

       Define('PROJECT_TITLE', 'MajordomoSL');
       Define('PROJECT_BUGTRACK', "bugtrack@smartliving.ru");

       if (@$_ENV["COMPUTERNAME"]) {
        Define('COMPUTER_NAME', strtolower($_ENV["COMPUTERNAME"])); 
       } else {
        Define('COMPUTER_NAME', 'mycomp');                       // Your computer name (optional)
       }


       Define('DOC_ROOT', dirname(__FILE__));              // Your htdocs location (should be detected automatically)

       Define('SERVER_ROOT', 'c:/_majordomo');
       

       if (@$_ENV["S2G_BASE_URL"]) {
        Define('BASE_URL', $_ENV["S2G_BASE_URL"]);
       } else {
        Define('BASE_URL', 'http://127.0.0.1:80');              // Your base URL:port (!!!)
       }


       Define('ROOT', DOC_ROOT."/");
       Define('ROOTHTML', "/");
       Define('PROJECT_DOMAIN', @$_SERVER['SERVER_NAME']);

       Define('ONEWIRE_SERVER', 'tcp://192.168.1.120:1234');    // 1-wire OWFS server

       /*
       Define('HOME_NETWORK', '192.168.0.*');                  // home network (optional)
       Define('EXT_ACCESS_USERNAME', 'user');                  // access details for external network (internet)
       Define('EXT_ACCESS_PASSWORD', 'password');
       */

       //Define('DROPBOX_SHOPPING_LIST', 'c:/data/dropbox/list.txt');  // (Optional)


   }


   //get from lib/loader.php
   protected function autoload()
   {

       //Define("THIS_URL", $_SERVER['REQUEST_URI']); //поиском по проекту не нашел зачем это нужно
      // liblary modules loader

      if ($libDir = @opendir("./lib")) {
        while (($file = readdir($libDir)) !== false) {
          if ((preg_match("/\.php$/", $file)) && ($file!="loader.php")) {
           include_once("./lib/$file");
          }
        }
        closedir($libDir);
      }

   }


   protected function loadSettings()
   {
      //$settings = SQLSelect('SELECT NAME, VALUE FROM settings');
      //$settings = SQLSelect('SELECT NAME, VALUE FROM settings');
      $settings = $this->dbConnection->getRepository('DbEntity\\SettingsEntity')->findAll();

      foreach ($settings as $setting)
      {
         Define('SETTINGS_' . $setting->NAME, $setting->VALUE);
         //var_dump('SETTINGS_' . $setting->NAME . "|" . $setting->VALUE);
      }

      if (defined('SETTINGS_SITE_TIMEZONE')) 
      {
         ini_set('date.timezone', SETTINGS_SITE_TIMEZONE);
         date_default_timezone_set(SETTINGS_SITE_TIMEZONE);
      }

         
   }

   protected function loadLanguage()
   {
      if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php')) 
         include_once (ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php');

      include_once (ROOT . 'languages/default.php');
   }



   public function getDbConnection()
   {
      return $this->dbConnection;
   }

   /*

   

	public function __construct($configName)
	{


	}


	public function loadSettings()
	{


	}*/



}

