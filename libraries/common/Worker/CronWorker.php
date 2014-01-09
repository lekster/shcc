<?php

namespace Worker;

use Doctrine\Common\ClassLoader;

//require_once 'pbr-lib-common/src/Doctrine/Common/ClassLoader.php';
require_once 'libraries/common/CronLock/class.CronLock.php';
require_once 'libraries/common/Config/class.Config.php';
require_once 'libraries/common/class.Exception.php';
//require_once 'libraries/common/ServiceLocator/class.ServiceLocator.php';

abstract class CronWorker
{

	protected $config = null;
	protected $logger = null;
	protected $cronLock = null;
	protected $isSigTerm = false;

	public function __construct($configName, $loggerIOCName = 'Logger', $cronlockIocName = 'CronLock')
	{
		$this->config = new \Immo_MobileCommerce_Config($configName);
		$this->logger	= $this->config->getIOCObject($loggerIOCName);
		$this->cronLock = $this->config->getIOCObject($cronlockIocName);
		//var_dump(GIRAR_BASE_DIR);die();
		///$classLoader = new ClassLoader('Src', 'pbr-serv-sms-sender/');
		//$classLoader->register();
	}

	public function stopWork()
	{
		//echo "exit";
		exit();
	}

	public function catchSigTerm()
	{
		$this->isSigTerm = true;
	}

	public function doWork($params)
	{
		$this->customPreInit($params);	

		if ($this->cronLock->isLocked())
		{
		    $this->logger->debug('CronLock', 'ALREADY LOCKED');
		    die("ALREADY LOCKED");
		}
		else
		{
		    $this->cronLock->lock();
		}

		//pcntl_signal_dispatch();
		declare(ticks=1); 
		pcntl_signal(SIGTERM, array($this, 'catchSigTerm'));

		try
		{
			$this->customAfterInit($params);
			$this->customBeforeDoWork($params);
			$this->checkRunConditions($params);

			$ret = 0;
			while (!$this->cronLock->isNeedToStopOrRestart() && !$this->isSigTerm && $ret != -1)
			{
				$ret = $this->work($params);
				$this->cronLock->heartbeat();
				//$this->logger->debug('run - ' . var_export($argv, 1));
				//sleep(1);
			}
			$this->customAfterDoWork($params);
		}
		catch(\Exception $e)
		{
			$this->logger->fatal('UNCATCHABLE EXCEPTION', $e);
		}
		$this->logger->debug('exit');
		$this->stopWork();

	}

	public abstract function work($params);

	public function customPreInit($params)	{ }

	public function customAfterInit($params)	{ }

	public function customBeforeDoWork($params)	{ }

	public function customAfterDoWork($params)	{ }

	public function checkRunConditions($params) { }

}
