<?php

namespace Worker;

use Doctrine\Common\ClassLoader;
use Daemonize\Daemonizator\Daemonizator;

require_once 'pbr-lib-common/src/Doctrine/Common/ClassLoader.php';
require_once 'pbr-lib-common/src/CronLock/class.CronLock.php';
require_once 'pbr-lib-common/src/ServiceLocator/class.ServiceLocator.php';

abstract class CronWorkerDaemonizable extends CronWorker
{
	
	public function __construct($configName, $loggerIOCName = 'Logger', $cronlockIocName = 'CronLock')
	{
		parent::__construct($configName, $loggerIOCName, $cronlockIocName);
		$classLoader = new ClassLoader('Daemonize', 'pbr-lib-common/src/');
		$classLoader->register();
	}

	public function doWork($params)
	{
		$this->customPreInit($params);	

		$pid = Daemonizator::daemonizeProcessS();
		//echo ("now in pid - $pid");

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

			while (!$this->cronLock->isNeedToStopOrRestart() && !$this->isSigTerm )
			{
				
				$this->work($params);
				$this->cronLock->heartbeat();
				
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

	
}
