<?php
/**
 * Immo_MQ_Connector
 * Абстрактный класс коннектор
 */

require_once 'pbr-lib-common/src/MQ/class.Message.php';

abstract class Immo_MQ_Connector
{
	protected $_server;
	protected $_login;
	protected $_password;
	protected $_heartbeat;
	protected $_messageXsd;
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
		$doc->loadXML($xml);
		$rez = true;
		//валидируем целиком
		if(!$doc->schemaValidate($this->_messageXsd))
		{
			$this->last_validate_error = 'Not validate _messageXsd';
			$rez = false;
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
			$dataXsd = $this->_dataXsd . ($this->_prefix === null ? '' : $this->_prefix . '.xsd');

			if (!$doc->schemaValidate($dataXsd))
			{
				$this->last_validate_error = 'Not validate _dataXsd';
				$rez = false;
			}
		}
		//var_dump(libxml_get_errors());
		libxml_use_internal_errors($lixmlErrors);
		return $rez;
	}
}