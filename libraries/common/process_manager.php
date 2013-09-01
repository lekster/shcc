#!/usr/bin/php
<?php


include '../htdocs/bootstrap.php';
chdir(GIRAR_BASE_DIR);

use Doctrine\Common\ClassLoader;
use Daemonize\Daemonizator\Daemonizator;
use Worker\CronWorker;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Src\Entity\ConfigEntity;

require_once 'pbr-lib-common/src/Doctrine/Common/ClassLoader.php';

$classLoader = new ClassLoader('Worker', 'pbr-lib-common/src/');
$classLoader->register();
$classLoader = new ClassLoader('Src', 'pbr-serv-sms-sender/');
$classLoader->register();


class MyWorker extends CronWorker
{

	protected $configEm;

	protected $configsArr = array();

	public function initDBAL($params = null)
	{
		$classLoader = new ClassLoader('Doctrine', 'pbr-lib-common/src/');
		$classLoader->register();

		$doctrineConfig = Setup::createAnnotationMetadataConfiguration(array(GIRAR_BASE_DIR . "/pbr-serv-sms-sender/Src/Entity/"), true);
		$conn = $this->config->get('doctrineDatasourceProcessDb', 'datasource');
		$this->configEm = EntityManager::create($conn, $doctrineConfig);

		$this->configEm->getConnection()->getWrappedConnection()->setAttribute(\PDO::ATTR_TIMEOUT, 4);
	}

	public function initConfigs($params)
	{
		//INIT CONFIGS

		$configs = $this->configEm->getRepository('Src\\Entity\\ConfigEntity')->findBy(array('is_active' => true));
		foreach($configs as $ent)
			$this->configsArr[$ent->key] = $ent->val;
		//var_dump($this->configsArr);die();

	}

	public function checkRunConditions($params)
	{
		if (!$this->transporter)
		{
			$this->logger->fatal('Transporter not exists');
			$this->stopWork();
		}

		if (!is_numeric($this->configsArr['TransportIdMin']) || !is_numeric($this->configsArr['TransportIdMax']) )
		{
			$this->logger->fatal('TransportIdMax OR TransportIdMin not exists in config');
        	throw new Exception("TransportIdMax OR TransportIdMin not exists in config");
        }

	}

	public function customBeforeDoWork($params)
	{
		$this->initDBAL($params);
		$this->initConfigs($params);
		//$this->checkRunConditions($params);
	}

	public function work($params)
	{
		//var_dump(__DIR__);
		$cmd = 'cd ' . GIRAR_BASE_DIR . "/pbr-serv-sms-sender/cron/ && " . './sms_sender_new.php resend SmsTransporter';
		//$cmd = __DIR__ . '/run.sh resend SmsTransporter';
		var_dump($cmd);
		echo exec($cmd);
		die();
		var_dump($t);
		sleep(20);
		$this->stopWork();
		echo "12314";
		sleep(1);	
	}
}


$worker = new MyWorker('pbr-serv-sms-sender/config/current/process_manager.php');
$worker->doWork($argv);

/*
модель


$cmd = 'cd ' . GIRAR_BASE_DIR . "/pbr-serv-sms-sender/cron/ && " . './sms_sender_new.php resend SmsTransporter';
//$cmd = __DIR__ . '/run.sh resend SmsTransporter';
var_dump($cmd);
echo exec($cmd);
die();



*/