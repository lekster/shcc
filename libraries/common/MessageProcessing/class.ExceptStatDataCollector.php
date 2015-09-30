<?php
require_once 'pbr-lib-common/src/MessageProcessing/abstract.MessageProcessing.php';
require_once 'pbr-lib-common/src/Helper/class.ConvertTransactionMessage.php';
require_once 'pbr-lib-common/src/Helper/class.ConvertTransactionToHDFS.php';
require_once 'pbr-lib-common/src/Helper/class.CacheHelper.php';
require_once 'pbr-lib-common/src/Hadoop/WebHdfs/WebHDFS.php';

class Immo_MessageProcessing_ExceptStatDataCollector extends Immo_MessageProcessing_Abstract
{
	protected $_facade = null;
	protected $_logger = null;
	protected $_config = null;
	protected $_cache;
	protected $_filename;
	
	const CACHE_EXPIRE_SERVICE = 10800; // Время хранения сервиса в секундах
	const CACHE_EXPIRE_PROCESSING = 10800; // Время хранения процессинга в секундах
	const CACHE_EXPIRE_PRODUCT = 10800; // Время хранения продукта в секундах
	const CACHE_EXPIRE_USER = 10800; // Время хранения юзера в секундах
	const CACHE_EXPIRE_PARTNER = 10800; // Время хранения партнера в секундах
	
	public function __construct()
	{
		$serviceLocator = Immo_MobileCommerce_ServiceLocator::getInstance();
		$this->_facade = $serviceLocator->getFacade();
		$this->_logger = $serviceLocator->getLogger();
		$this->_config = $serviceLocator->getConfig();
		// Создаем объект кеш-хелпера
		$this->_cache = new Immo_MobileCommerce_CacheHelper();
	}

	public function process(Immo_MQ_Message $message)
	{
		$ok = false;
		$transaction = null;

		try {
			$transaction = Immo_Helper_ConvertTransactionMessage::convertMessageToTransaction($message);
			if(!$transaction) throw new Exception('Message is not correct');
			// Дата создания транзакции
			$dateCreate = substr($transaction->getDateCreate(),0,19);
			// Дата начала вчерашнего дня
			$yesterday = date('Y-m-d 00:00:00', strtotime("-1 day"));
			// Дата начала сегодняшнего дня
			$today = date('Y-m-d 00:00:00');
			// Время запуска крона, который сохраняет на HDFS
			$cronHour = $this->_config->get('FinalStatDataSenderCronHour', 'Global');
			// Текущий час
			$curHour = date('G');
			// Проверяем попадет ли транзакция под крон-финализатор или она была дослана и ее придется ексептить
			if($dateCreate<$yesterday || ($dateCreate<$today && $curHour>$cronHour))
			{
				$this->_logger->debug('Found except messageTransaction', $transaction);
				// Если попали сюда, значит это досыл статуса на дату которую финализатор уже обработал => нужно сохранитть в особые
				$this->_filename = 'exceptStatData_'.substr($dateCreate,0,10).'.log';
				$ok = $this->saveAsExcept($transaction);
			}
			else{
				// Если попали сюда, значит транзакция будет обработана стандартным кроном-финализатором
				return true;
			}
			return $ok;
		}
		catch (ImmoDatabaseException $e){
			return $e;
		}
		catch (PhpAmqpLib_Exception_AMQPException $e){
			return $e;
		}
		catch (Exception $e){
			$this->_logger->error('Exception in message processing', $e);
		}
	}


	protected function saveAsExcept($transaction)
	{
		// Так как в сообщении не все данные по транзакции, вытаскиваем её из БД
		$transactionFromDb = $this->_facade->getTransactionByGuid($transaction->getTransactionGuid());
		if(!$transactionFromDb) throw new Exception('Failed to get transaction from DB');
		$this->_logger->debug('Transaction from DB', $transactionFromDb);
		// Вытаскиваем связанные таблицы через кеш-хелпер
		$sKey = 'service_'.$transactionFromDb->getServiceId();
		$service = $this->_cache->get($sKey);
		if(!isset($service)){
			$service = $this->_facade->getServiceById($transactionFromDb->getServiceId(),false);
			$this->_cache->set($sKey, $service, self::CACHE_EXPIRE_SERVICE);
		}
		$pKey = 'processing_'.$service->getProcessingId();
		$processing = $this->_cache->get($pKey);
		if(!isset($processing)){
			$processing = $this->_facade->getProcessingById($service->getProcessingId(),false);
			$this->_cache->set($pKey, $processing, self::CACHE_EXPIRE_PROCESSING);
		}
		$prodKey = 'product_'.$transactionFromDb->getProductId();
		$product = $this->_cache->get($prodKey);
		if(!isset($product)){
			$product = $this->_facade->getProductById($transactionFromDb->getProductId(),false);
			$this->_cache->set($prodKey, $product, self::CACHE_EXPIRE_PRODUCT);
		}
		$uKey = 'user_'.$transactionFromDb->getUserId();
		$user = $this->_cache->get($uKey);
		if(!isset($user)){
			$user = $this->_facade->getUserById($transactionFromDb->getUserId(),false);
			$this->_cache->set($uKey, $user, self::CACHE_EXPIRE_USER);
		}
		$parKey = 'partner_'.$transactionFromDb->getPartnerId();
		$partner = $this->_cache->get($parKey);
		if(!isset($partner)){
			$partner = $this->_facade->getPartnerById($transactionFromDb->getPartnerId(),false);
			$this->_cache->set($parKey, $partner, self::CACHE_EXPIRE_PARTNER);
		}
		// Преобразовываем объект транзакции в массив
		$transactionFromDb = $transactionFromDb->toArray();
		// Дополняем транзакцию данными связанных таблиц
		$transactionFromDb['service_name'] = $service->getServiceName();
		$transactionFromDb['service_sms_prefix'] = $service->getSmsPrefix();
		$transactionFromDb['processing_name'] = $processing->getName();
		$transactionFromDb['product_name'] = $product->getName();
		$transactionFromDb['user_name'] = $user->getUsername();
		$transactionFromDb['partner_name'] = $partner->getName();
		// Конвертируем данные в HDFS формат и сохраняем их в файл для отправки
		$hdfsRow = Immo_Helper_ConvertTransactionToHDFS::convertTransactionToHDFS($transactionFromDb);
		// Открываем дневной файл с исключительными транзакциями на запись
		$filepath = $this->_config->get('ExceptStorage', 'Global');
		$this->_logger->debug('Open file to write: '.$filepath.$this->_filename);
		$handle = fopen($filepath.$this->_filename, 'a+');
		$successWrite = fwrite($handle, $hdfsRow."\n");
		fclose($handle);
		$this->_logger->debug('Close file: '.$filepath.$this->_filename);
		if($successWrite===false){
			$this->_logger->warn('Writing error: '.$transaction->getTransactionGuid());
			return false;
		}
		return true;
	}
}