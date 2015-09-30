<?php

namespace Worker;

use Doctrine\Common\ClassLoader;

require_once 'pbr-lib-common/src/Worker/CronWorker.php';
require_once 'pbr-lib-common/src/ServiceLocator/class.ServiceLocator.php';
require_once 'pbr-lib-common/src/Helper/class.ESBConnectorHelper.php';


abstract class CronWorkerEsb extends CronWorker
{

	protected $postXmlConfigurator;
	protected $mqConnector;
	protected $esbConnector;
	protected $moduleName;
	protected $lastSendHeartbeatTime;

	public function __construct($moduleName, $configName, $loggerIOCName = 'Logger', $cronlockIocName = 'CronLock', 
		$postXmlConfiguratorIocName = 'PostXmlESBConnector', $mqConnectorIocName = 'MqESBConnector')
	{
		\Immo_MobileCommerce_ServiceLocator::getInstance($configName);
		$this->moduleName = $moduleName;
		$this->config = new \Immo_MobileCommerce_Config($configName);
		$this->logger	= $this->config->getIOCObject($loggerIOCName);
		$this->cronLock = $this->config->getIOCObject($cronlockIocName);
		$this->postXmlConfigurator = $this->config->getIOCObject($postXmlConfiguratorIocName);
		//$this->mqConnector = $this->config->getIOCObject($mqConnectorIocName);
		$this->esbConnector = new \ESBConnectorHelper($this->moduleName, $mqConnectorIocName, $postXmlConfiguratorIocName);
		$this->lastSendHeartbeatTime = null;
	}

	public function getServiceInfo($methodName, $type)
	{
		return $this->esbConnector->getServiceInfo($methodName, $type);
	}

    public function getNamedConf($name, $isFromCache = true)
    {
    	return $this->esbConnector->getNamedConf($name, $isFromCache);
    }

    public function sendErrorEvent($event)
    {
    	$this->esbConnector->sendErrorEvent($event);
    }

	public function doWork($params = array())
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

			while (!$this->cronLock->isNeedToStopOrRestart() && !$this->isSigTerm)
			{
				$this->work($params);
				$this->cronLock->heartbeat();
				if (is_null($this->lastSendHeartbeatTime) || ($this->lastSendHeartbeatTime < time() - 60 ) )
				{
					$this->esbConnector->sendHeartBeat();
					$this->lastSendHeartbeatTime = time();
				}
				//$this->logger->debug('run - ' . var_export($argv, 1));
				//sleep(1);
			}
			$this->customAfterDoWork($params);
		}
		catch(\Exception $e)
		{
			$this->logger->fatal('UNCATCHABLE EXCEPTION', $e);
			$this->esbConnector->sendErrorEvent('UNCATCHABLE EXCEPTION|' . $e->getMessage());
		}
		$this->logger->debug('exit');
		$this->stopWork();

	}


}