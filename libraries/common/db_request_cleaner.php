#!/usr/bin/php
<?php

include '../htdocs/bootstrap.php';
chdir(GIRAR_BASE_DIR);

use Doctrine\Common\ClassLoader,
	Worker\CronWorker,
	Worker\CronWorkerDaemonizable,
	Doctrine\ORM\Tools\Setup,
	Doctrine\ORM\EntityManager;
	

require_once 'pbr-lib-common/src/Doctrine/Common/ClassLoader.php';

$classLoader = new ClassLoader('Worker', 'pbr-lib-common/src/');
$classLoader->register();
$classLoader = new ClassLoader('Src', 'pbr-serv-sms-sender/');
$classLoader->register();


class MyWorker extends CronWorker
{
	protected $configEm;
	protected $requestEm;

	protected $configsArr = array();

	public function initDBAL($params = null)
	{
		$classLoader = new ClassLoader('Doctrine', 'pbr-lib-common/src/');
		$classLoader->register();

		$doctrineConfig = Setup::createAnnotationMetadataConfiguration(array(GIRAR_BASE_DIR . "/pbr-serv-sms-sender/Src/Entity/"), true);
		$conn = $this->config->get('doctrineDatasourceProcessDb', 'datasource');
		$this->configEm = EntityManager::create($conn, $doctrineConfig);

		$conn = $this->config->get('doctrineDatasourceRequestDb', 'datasource');
		$this->requestEm = EntityManager::create($conn, $doctrineConfig);
		
		//$this->configEm->getConnection()->getWrappedConnection()->setAttribute(\PDO::ATTR_TIMEOUT, 4);
		//$this->requestEm->getConnection()->getWrappedConnection()->setAttribute(\PDO::ATTR_TIMEOUT, 4);
	}

	public function initConfigs($params)
	{
		//INIT CONFIGS

		$configs = $this->configEm->getRepository('Src\\Entity\\ConfigEntity')->findBy(array('is_active' => true));
		foreach($configs as $ent)
			$this->configsArr[$ent->key] = $ent->val;
	}

	public function checkRunConditions($params)
	{
				
	}

	public function customBeforeDoWork($params)
	{
		$this->initDBAL($params);
	}


	public function work($params)
	{
		$this->logger->info("Start Clean");
		$totalRowCount = 0;
		$sql = "delete from request where request_guid in
				(
					select request_guid from request where date_create < now() - '2 days'::interval
					limit 100 
				);";
		do
		{
			$affectedRows = $this->requestEm->getConnection()->executeUpdate($sql);
			$totalRowCount += $affectedRows;
			sleep($this->config->get('SleepSec', 'CleanerConfig'));
		} while ($affectedRows > 0);

		$this->logger->info("End Clean|Delete Records Count - $totalRowCount");
		$this->stopWork();
	}
}

$params = explode(' ', @$argv[1]);
$params = array();
$worker = new MyWorker('pbr-serv-sms-sender/config/current/db_request_cleaner.php');
$worker->doWork($params);
