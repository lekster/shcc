#!/usr/bin/php
<?php

include '../htdocs/bootstrap.php';
chdir(GIRAR_BASE_DIR);

use Doctrine\Common\ClassLoader,
	Daemonize\Daemonizator\Daemonizator,
	Worker\CronWorker,
	Worker\CronWorkerDaemonizable,
	Src\Transporter\ImmoPlatformSms,
	Doctrine\ORM\Tools\Setup,
	Doctrine\ORM\EntityManager,
	Src\Entity\RequestEntity,
	Src\Entity\ReturnAddressEntity,
	Src\Helper\CalculateReturnAddress,
	Src\Entity\ConfigEntity;

require_once 'pbr-lib-common/src/Doctrine/Common/ClassLoader.php';
require_once 'pbr-lib-common/src/MQ/class.Message.php';

$classLoader = new ClassLoader('Worker', 'pbr-lib-common/src/');
$classLoader->register();
$classLoader = new ClassLoader('Src', 'pbr-serv-sms-sender/');
$classLoader->register();


//class MyWorker extends CronWorkerDaemonizable
//либо запуск через nohup php -f ex1.php &>/dev/null &
class MyWorker extends CronWorker
{
	protected $configEm;
	protected $requestEm;

	protected $transporter;

	protected $mqConector;
	protected $mqReturnAddrConector;

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
		//Init Transporter
		$transporterIocName = $params[1];
		$transporter = $this->config->getIOCObject($transporterIocName);
		if ($transporter)
		{
			$baseCallbackUrl = $transporter->getCallbackUrl();
			$callbackUrl = sprintf($baseCallbackUrl, trim(`hostname`));
			$transporter->setCallbackUrl($callbackUrl);
			$this->transporter = $transporter;
		}

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
			throw new Exception('Transporter not exists');
		}

		if (!@is_numeric($this->configsArr['TransportIdMin']) || !@is_numeric($this->configsArr['TransportIdMax']) )
		{
			$this->logger->fatal('TransportIdMax OR TransportIdMin not exists in config');
        	throw new Exception("TransportIdMax OR TransportIdMin not exists in config");
        }

        if (!@is_numeric($this->config->get('WaitmessageSleepSec', 'SmsSenderConfig')))
		{
			$this->logger->fatal('WaitmessageSleepSec not exists in config');
        	throw new Exception("WaitmessageSleepSec not exists in config");
        }

        if (!@is_numeric($this->config->get('IntermessageSleepMs', 'SmsSenderConfig')))
		{
			$this->logger->fatal('IntermessageSleepMs not exists in config');
        	throw new Exception("IntermessageSleepMs not exists in config");
        }		

        if (!@is_dir($this->config->get('PathToScsFilesDir', 'SmsSenderConfig')))
		{
			$this->logger->fatal('IntermessageSleepMs not exists in config');
        	throw new Exception("IntermessageSleepMs not exists in config");
        }

        
				
	}

	public function initMQ($params)
	{
		$queueName = $params[0];
		$this->mqConector = $this->config->getIOCObject('SmsSenderQueueConnector');
		$this->mqConector->setChannelName($queueName);
		$this->mqReturnAddrConector = $this->config->getIOCObject('SmsSenderReturnConnector');
	}

	public function customBeforeDoWork($params)
	{
		$this->initDBAL($params);
		$this->initConfigs($params);
		$this->initMQ($params);
	}

	public function touchScsMonitFile($params)
	{
		$str = basename(__FILE__) . $params[0] . $params[1] . @$params[2]; 
		$fileName = $this->config->get('PathToScsFilesDir', 'SmsSenderConfig') . "/" . md5($str) . ".scs";
		touch($fileName);
	}

	/**
	*
	*	Обрабатывает MQ сообщение 
	*	return Объект-request из БД
	*
	*/
	public function processMsg($msg)
	{
			$data= $msg->getMQMessageData();
			$data = $data['SmsMessage'];

			//var_dump($data);die();
	        $smsId = mt_rand($this->configsArr['TransportIdMin'], $this->configsArr['TransportIdMax']);
	        $returnAddress = $msg->getReturnAddress();
	        $requestGuid = $data['SmsGuid'];
	        $correlationId = $msg->getCorrelationID();

	        //создаем реквест в БД
	        $request = new RequestEntity();
	        $request->request_guid = $requestGuid;
	        $request->transport_id = $smsId;
	        $request->return_address_id =  CalculateReturnAddress::getReturnAddressId($returnAddress, $this->requestEm); 
	        $request->date_create = date('c');//date('Y-m-d H:i:s');
	        $request->correlation_id = $correlationId;
	        $request->msisdn = $data['Msisdn'];
	        
	        //сохряняем       
	        try
	        {
	        	$this->requestEm->persist($request);
	        	$this->requestEm->flush();
	        	//делаем ack, если все хорошо	
		        try
		        {
		        	$this->mqConector->ack($msg);
		        }
		        catch(PhpAmqpLib\Exception\AMQPRuntimeException $e)
		        {
		        	//если отваливается коннект с реббитом, то мы попадаем сюда
		        	$this->logger->fatal('AMQPRuntimeException EXCEPTION|ACK Fail|' . $e->getMessage());
		        	$this->logger->fatal('|ACK Fail for|' . var_export($request));
		        	//если ack не прошел: удаляем запись из БД по GUID и выходим
		        	$this->requestEm->remove($request);
		   			$this->requestEm->flush();
		        	$request = -1;
		        	//$this->stopWork();
		        }

	        }
	        catch(Doctrine\DBAL\DBALException $e)
	        {
	        	//тут вывалится exception если например пересекутся transport_id
	        	//Fatal error: Uncaught exception 'PDOException' with message 'SQLSTATE[23000]: Integrity constraint violation: 19 column request_guid is not unique'
	        	//Integrity constraint violation: 19 column request_guid is not unique
	        	$this->logger->error('DBALException EXCEPTION|' . $e->getMessage());
	        	//переинициализируем DBAL, он автоматически закрывает всех менеджеров при эксепшене
	        	$this->initDBAL();
	        	try
	        	{
	        		$this->mqConector->nack($msg);
	        	}
	        	catch(PhpAmqpLib\Exception\AMQPRuntimeException $e)
		        {
		        	//если отваливается коннект с реббитом, то мы попадаем сюда
		        	$this->logger->fatal('AMQPRuntimeException EXCEPTION|NACK Fail|' . $e->getMessage());
		        	//если nack не прошел, значит что-то в базе уже есть скорее всего, удалять нельзя, пишем в лог и ничего не делаем
		        	$request = -1;
		        }
	        	//var_dump($e);
	        	$request = null;
	        }	

	        return $request;
	}

	
	public function work($params)
	{
		//получаем очередное сообщение
		//$msg = $connector->getWithoutValidate();
		$msg = $this->mqConector->get();
		if ($msg)
		{
			$this->logger->info('Process Msg', $msg);			
			$request = $this->processMsg($msg);
			$this->logger->debug('Return request', $request);

			if (!is_object($request) && $request == -1)
				$this->stopWork();
			
			$data= $msg->getMQMessageData();
			$data = $data['SmsMessage'];

			if (is_object($request))
	        {
		        //отправляем
		        $this->logger->info('Send Msg - ' . $request->request_guid);
				$ret = $this->transporter->send($request->msisdn, $data['Text'], $data['AlphaNumber'], $request->transport_id);
				
				if ($ret)
				{
					$request->status = RequestEntity::STATUS_SENDED_TO_TRANSPORT;
					$this->requestEm->persist($request);
		        	$this->requestEm->flush();
		        	$this->logger->info('SendOK');		
				}
				else
				{
					//отправка не прошла
					$request->status = RequestEntity::STATUS_FAIL;
					$this->requestEm->persist($request);
		        	$this->requestEm->flush();		
					//возможно надо зачистить таблицу от ненужной записи
					//отправляем коллбек в очередь коллбеков если необходимо
					//пишем в лог, что отправка провалена
					$this->logger->warn('Send to transport err|GUID-' . $request->request_guid);

		        	$ret = CalculateReturnAddress::sendSmsStatusCallback($request, $this->requestEm, $this->mqReturnAddrConector);	
		        	if (!$ret)
		        	{
		        		$this->logger->error('ERROR DURING SEND TO CALLBACK |' . "reqId-" . $request->request_guid . "|GUID-" . $request->request_guid);
		        	}
				}
			}
			
		}
		else
		{
			//если ничего не получили отдыхаем N сек
			sleep($this->config->get('WaitmessageSleepSec', 'SmsSenderConfig'));		
		}
		usleep($this->config->get('IntermessageSleepMs', 'SmsSenderConfig'));	
		$this->touchScsMonitFile($params);
	}
}

$params = explode(' ', $argv[1]);
$worker = new MyWorker('pbr-serv-sms-sender/config/current/sms_sender.php');
$worker->doWork($params);

//Запуск
//php -f sms_sender_new.php 1 SmsTransporter
//php -f sms_sender_new.php deadQ SmsTransporter
//либо запуск через nohup php -f ex1.php &>/dev/null &
// nohup ./sms_sender_new.php resend SmsTransporter &>/dev/null &