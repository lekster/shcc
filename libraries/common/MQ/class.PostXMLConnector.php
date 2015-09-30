<?php

require_once 'pbr-lib-common/src/MQ/abstract.Connector.php';
require_once 'pbr-lib-common/src/MQ/class.Message.php';
require_once 'pbr-lib-common/src/Caller/class.Caller.php';


/**
 * Immo_MQ_PostXMLConnector
 * Класс для отправки и получения XML сообщений
 */
class Immo_MQ_PostXMLConnector extends Immo_MQ_Connector
{
	private $_xml;

	private $_timeout = null;


	public function ack(Immo_MQ_Message $message)
	{
		header('HTTP/1.0 202 Accepted');
	}

	public function beginT() {}
	public function commitT($transactioId) {}
	public function abortT($transactioId) {}
	public function subscribe() {}
	public function unsubscribe() {}

	public function nack(Immo_MQ_Message $message)
	{
		header('HTTP/1.0 500 Internal Server Error');
	}

	public function nacknowledge()
	{
		header('HTTP/1.0 500 Internal Server Error');
	}


	public function send(Immo_MQ_Message $message)
	{
		$url = $this->_channelName;

		if ($this->_method && $url)
			$url .= (strpos($url, '?') === false ? '?' : '&') . 'type=' . $this->_method;

		//создаем курл соединение по адресу из $_channelName
		$caller = new Caller($url, 202);

		//добавляем в пост поля наш XML и указываем content-type
		$caller->setPost($message->getAsXml(),'text/xml');

		if ($this->_timeout !== null) $caller->setTimeOut($this->_timeout);

		//делаем запрос, указав что это пост
		$result = $caller->call(array(),true);

		if ($result === null) return false;

		$this->_xml = $result;

		if ($this->_method) $this->_prefix = $this->_method . 'Response';

		return true;
	}

	public function get()
	{
		//вытаскиваем XML
		if ($this->_xml)
			$xml = $this->_xml;
		else
		{
			$xml = file_get_contents('php://input');

			if (!$xml)
				throw new Exception('ERROR: there is no XML');
		}

		//при успешной валидации XML создаем message на его основе
		if (!$this->validate($xml))
		{
			Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger()->info('XML not validated', libxml_get_last_error());

			throw new Exception('ERROR: XML not validated');
		}

		return new Immo_MQ_Message($xml);
	}

	public function getWithoutValidate()
	{
		if ($this->_xml) return new Immo_MQ_Message($this->_xml);

		$xml = file_get_contents('php://input');

		return $xml ? new Immo_MQ_Message($xml) : null;
	}


	public function getRawXml()
	{
		return $this->_xml;
	}


	public function setTimeOut($timeout)
	{
		return $this->_timeout = $timeout;
	}
}
