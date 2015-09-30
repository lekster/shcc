<?php

require_once 'pbr-lib-common/src/MQ/class.AmqpConnector.php';
require_once 'pbr-lib-common/src/MessageProcessing/abstract.MessageProcessing.php';
require_once 'pbr-lib-common/src/Caller/class.Caller.php';

class Immo_MessageProcessing_Common extends Immo_MessageProcessing_Abstract {

	protected $logger = null;
	protected $config = null;
	protected $connector = null;
	protected $handlersCache = array();

	public function __construct($connector) {
		$this->config = Immo_MobileCommerce_ServiceLocator::getInstance()->getConfig();
		$this->logger = Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger();
		$this->connector = $connector;
	}

	protected function getXsdSchemaFilePath($formatIndicator, $version)
	{
		//получаем из локального кеша (в локальном кеше файлы xsd хрянятся в формате [FormatIndicator]_[Vesion].xsd)
		$cachePath = $this->config->get('LocalXsdCache', 'MessageProcessingCommon');	
		$filePath = $cachePath . "/" . "{$formatIndicator}_{$version}" . ".xsd";
		$storageBaseUrl = $this->config->get('GlobalXsdStorageBaseUrl', 'MessageProcessingCommon'); 
		if (file_exists($filePath))
		{
			//если нашли - смотрим дату последнего изменения файла локального кеша
			$lastModTime = filemtime($filePath);
			if (!$lastModTime)
			{
				return null;
			}
			//если она отстает от текущего времени больше чем на 1 день, то вычисляем md5 далее запрашиваем md5 у глобального хранилища
			//если они сходятся - то делаем touch на файл, если нет - пишем ошибку и возвращаем null 
			if (time() - $lastModTime > 86400)
			{
				$caller = new Caller($storageBaseUrl . "XsdMd5/$formatIndicator/$version");	
				$result = $caller->call();
				if ($result)
				{	
					$result = json_decode($result);
					$md5FromGlobal = @$result->Result->fileMd5;

					$md5 = md5_file($filePath);
					if ($md5 == $md5FromGlobal)
					{
						touch($filePath);
					}
					else
					{
						$this->logger->error("MD5 NOT VALID|filePath-$filePath|formatIndicator-$formatIndicator|version-$version");
						return null;
					}
				}
				else
				{
					$this->logger->error("UNABLE TO GET MD5, TRY Exists schema|filePath-$filePath|formatIndicator-$formatIndicator|version-$version");
				}
			}
			
		}
		else
		{
			
			$caller = new Caller($storageBaseUrl . "Xsd/$formatIndicator/$version");
			$result = $caller->call();
			if ($result)
				$result = json_decode($result);
			//если там нет - пробуем получить из глобального хранилища и сохранить к себе
			$content = base64_decode(@$result->Result->fileBase64);
			$md5Glob = @$result->Result->fileMd5;
			if (md5($content) == $md5Glob)
			{
				file_put_contents($filePath, $content);
				return $filePath;
			}
			//если и там нет - возвращаем null
			return null;
		}	
		return $filePath;
	}

	protected function getHandlerClassByMsg(Immo_MQ_Message $message)
	{
		$formatIndicator = $message->getFormatIndicator();
		$version = $message->getVersion();

		$xsdSchemaFilePath = $this->getXsdSchemaFilePath($formatIndicator, $version);
		if ($xsdSchemaFilePath)
		{
			$validateResult = $this->validate($message->getAsXml(), $xsdSchemaFilePath);
			if (!$validateResult)
				return false;
			$conf = $this->config->get('Handlers', 'MessageProcessingCommon');
			if (!is_array(@$conf[$formatIndicator]))
			{
				$this->logger->error("HADLER SECTION NOT FOUND|formatIndicator-$formatIndicator|version-$version");
				return false;
			}
			//собираем путь до обработчика
			$basePath = $conf[$formatIndicator]['BasePath'];
			$className = $conf[$formatIndicator]['ClassName'];
			$handlerFilePath = $basePath . "/$version/" . "class." . $className . ".php";
			$ret = @include_once ($handlerFilePath);
			if (!$ret)
			{
				$this->logger->error("HANDLER FILE NOT FOUND", $handlerFilePath);
				return false;
			}
			//поискать в кеше
			if (isset($this->handlersCache[$handlerFilePath]))
				return $this->handlersCache[$handlerFilePath];
			$this->handlersCache[$handlerFilePath] = new $className();
			return $this->handlersCache[$handlerFilePath];
		}
		else
		{
			$this->logger->error("XSD SCHEMA NOT FOUND|formatIndicator-$formatIndicator|version-$version");
			return false;
		}

	}

	protected function validate($xml, $xsdSchemaFilePath)
	{
		$this->connector->last_validate_error = null;

		$lixmlErrors = libxml_use_internal_errors(true);
		//создаем объект, с помощью которого будем работать с XML
		$doc = new DOMDocument();
		//загружаем весь XML
		$doc->loadXML($xml);
		$rez = true;
		//валидируем целиком
		if(!$doc->schemaValidate($this->connector->_messageXsd))
		{
			$this->connector->last_validate_error = 'Not validate _messageXsd';
			$rez = false;
			// Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger()->warn('Not validate _messageXsd');
		}
		//вытаскиваем отдельно тело
		$headFirst = strpos($xml,'<MQMessage>');
		//ищем начало тела по рутовому элементу тела (15 - количество сиволов в теге)
		$bodyFirst = strpos($xml,'<MQMessageData>')+15;
		//ищем конец тела по закрывающемуся руту тела
		$bodyLast = strpos($xml,'</MQMessageData>');
		//вычисляем длинну тела: начало - конец
		$bodyLen = $bodyLast - $bodyFirst;
		$bodyXml = substr($xml,0,$headFirst);
		$bodyXml .= substr($xml,$bodyFirst,$bodyLen);
		//загружаем тело
		$doc->loadXML($bodyXml);
		//валидируем тело
		if ($rez)
		{			
			if(!$doc->schemaValidate($xsdSchemaFilePath))
			{
				$this->connector->last_validate_error = 'Not validate _dataXsd';
				$rez = false;
			}
		}
		libxml_use_internal_errors($lixmlErrors);
		$validateErrorDescription = libxml_get_last_error(); //get validate error description
		if (!$rez)
		{
			$this->logger->error("VALIDATE XML ERROR", $validateErrorDescription);
		}
		return $rez;
	}


	public function process(Immo_MQ_Message $message) 
	{
		$handler = $this->getHandlerClassByMsg($message);
		if (!$handler)
			return false;
		return $handler->process($message);
	}

	public function clear()
	{
		foreach ($this->handlersCache as $value) 
		{
			$value->clear();
		}
		unset($this->handlersCache);
	}
}
