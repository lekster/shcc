<?php
/**
 * Immo_MQ_Connector
 * Абстрактный класс коннектор
 */

require_once 'pbr-lib-common/src/MQ/class.Message.php';
require_once 'pbr-lib-common/src/MessageProcessing/class.CommonProcessing.php';

abstract class Immo_MQ_Connector
{
	protected $_server;
	protected $_login;
	protected $_password;
	protected $_heartbeat;
	public $_messageXsd;
	protected $_dataXsd;
	protected $_channelName;
	protected $_port;

	protected $_method = null;
	protected $_prefix = null;

	public $last_validate_error = null;


	public function __construct($server,$login,$password,$heartbeat,$messageXsd,$dataXsd,$channelName)
	{
		//заполняем свойства класса
		$this->_server = $server;
		$this->_login = $login;
		$this->_password = $password;
		$this->_heartbeat = $heartbeat;
		$this->_messageXsd = $messageXsd;
		$this->_dataXsd = $dataXsd;
		$this->_channelName = $channelName;
	}

	abstract public function send(Immo_MQ_Message $message);
	abstract public function get();
	abstract public function getWithoutValidate();
	abstract public function ack(Immo_MQ_Message $message);
	abstract public function nack(Immo_MQ_Message $message);
	abstract public function beginT();
	abstract public function commitT($transactioId);
	abstract public function abortT($transactioId);
	abstract public function subscribe();
	abstract public function unsubscribe();

	public function setChannelName($channelName)
	{
		$this->_channelName = $channelName;

		return $this;
	}

	public function setDataXsd($xsd)
	{
		$this->_dataXsd = $xsd;

		return $this;
	}


	public function getMethod()
	{
		return $this->_method;
	}

	public function setMethod($method)
	{
		$this->_method = $method;

		return $this;
	}

	public function getPrefix()
	{
		return $this->_prefix;
	}

	public function setPrefix($prefix)
	{
		$this->_prefix = $prefix;

		return $this;
	}


	public function validate($xml)
	{
		$this->last_validate_error = null;

		$lixmlErrors = libxml_use_internal_errors(true);
		//создаем объект, с помощью которого будем работать с XML
		$doc = new DOMDocument();
		//загружаем весь XML
		$doc->loadXML($xml, LIBXML_PARSEHUGE);
		$rez = true;
		//валидируем целиком
		if(!$doc->schemaValidate($this->_messageXsd))
		{
			$this->last_validate_error = 'Not validate _messageXsd|' . str_replace(array("\r", "\n", "\t"), "", var_export(libxml_get_errors(), true) );
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
		$doc->loadXML($bodyXml, LIBXML_PARSEHUGE);
		//валидируем тело
		if ($rez)
		{
			$dataXsd = $this->_dataXsd . ($this->_prefix === null ? '' : $this->_prefix . '.xsd');
			// Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger()->debug($dataXsd);

			if(!$doc->schemaValidate($dataXsd))
			{
				$this->last_validate_error = 'Not validate _dataXsd|' .  str_replace(array("\r", "\n", "\t"), "", var_export(libxml_get_errors(), true) );
				$rez = false;
				// Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger()->warn('Not validate _dataXsd');
			}
		}
		// var_dump(libxml_get_errors());
		libxml_use_internal_errors($lixmlErrors);
		return $rez;
	}
	
	public function processingMessages(Immo_MessageProcessing_Abstract $messageProcessing, $limitMessages = 100)
	{
		$logger = Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger();

		$count = 0;

		while ($count < $limitMessages)
		{
			$message = null;
			$ok = false;

			try {
				$this->last_validate_error = null;

				$message = $this->get();

				if (!$message) break;

				$ok = $messageProcessing->process($message);
			}
			catch (Exception $e)
			{
				if ($this->last_validate_error)
					$logger->error('last_validate_error', $this->last_validate_error);

				$logger->warn('Exception', $e);
			}

			$count++;

			if ($ok)
			{
				if ($ok instanceof Exception) throw $ok;

				$this->ack($message);
			}
			elseif ($message)
			{
				$logger->debug('Message NACK', $message);

				$this->nack($message);
			}
		}

		$messageProcessing->clear();

		return $count;
	}
	
	public function processingMessagesVersioning($limitMessages = 100)
	{
		$logger = Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger();
		$messageProcessing = new Immo_MessageProcessing_Common($this);

		$count = 0;

		while ($count < $limitMessages)
		{
			$message = null;
			$ok = false;

			try {
				$this->last_validate_error = null;

				$message = $this->getWithoutValidate();
				//var_dump($message);die();
				if (!$message) break;

				$ok = $messageProcessing->process($message);
			}
			catch (Exception $e)
			{
				if ($this->last_validate_error)
					$logger->error('last_validate_error', $this->last_validate_error);

				$logger->warn('Exception', $e);
			}

			$count++;

			if ($ok)
			{
				if ($ok instanceof Exception) throw $ok;

				$this->ack($message);
			}
			elseif ($message)
			{
				$logger->debug('Message NACK', $message);

				$this->nack($message);
			}
		}

		$messageProcessing->clear();

		return $count;
	}

    public function processingMessagesBatch(Immo_MessageProcessing_Abstract $messageProcessing, $limitMessages = 100)
    {
        $logger = Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger();

        $count = 0;
        $messages = array();

        while ($count < $limitMessages)
        {
            $message = null;
            $ok = false;

            try {
                $this->last_validate_error = null;

                $message = $this->get();

                if (!$message) break;

                $messages[] = $message;
            }
            catch (Exception $e)
            {
                if ($this->last_validate_error)
                    $logger->error('last_validate_error', $this->last_validate_error);

                $logger->warn('Exception', $e);
            }

            $count++;
        }

        if ($messages)
        {
            $messageProcessing->processBatch($this, $messages);

            $messageProcessing->clear();
        }

        return $count;
    }
}