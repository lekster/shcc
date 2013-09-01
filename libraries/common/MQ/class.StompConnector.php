<?php
/**
 * Immo_MQ_StompConnector
 * Класс для работы с брокером сообщений MessageMQ по STOMP протоколу
 */

require_once 'pbr-lib-common/src/MQ/abstract.Connector.php';
require_once 'pbr-lib-common/src/MQ/src/Stomp.php';

class Immo_MQ_StompConnector extends Immo_MQ_Connector
{
	private $_connection;

	public function __construct($server,$login,$password,$heartbeat,$messageXsd,$dataXsd,$channelName)
	{
		//выполняем конструктор родителя
		parent::__construct($server,$login,$password,$heartbeat,$messageXsd,$dataXsd,$channelName);
		//создаем объект для работы с RabbitMQ по средствам STOMP протокола
		$this->_connection = new StompConnection($this->_server);
		//подключаемся к брокеру с логином, паролем и верменем жизни сообщений
		$this->_connection->connect($this->_login,$this->_password,$this->_heartbeat);
	}

	public function send(Immo_MQ_Message $message)
	{
		//получаем ID транзакции
		$transactionId = $message->getMQTransactionId();
		$properties = ($transactionId ? array("transaction" => $transactionId) : null);
		//если сообщение отправляется в транзакции то указываем ID транзакции при добавлении в очередь
		$this->_connection->send($this->_channelName, $message->getAsXml(), $properties);
	}

	public function beginT()
	{
		//создаем уникальный ID транзакции
		$transactionId = uuid_create();
		//стартуем транзакцию с уникальным ID
		$this->_connection->begin($transactionId);
		//возвращаем ID транзакции, чтобы далее использовать при сенде, комите и аборте
		return $transactionId;
	}

	public function commitT($transactionId)
	{
		//комитим указанную транзакцию
		$this->_connection->commit($transactionId);
	}

	public function abortT($transactionId)
	{
		//абортим указанную транзакцию
		$this->_connection->abort($transactionId);
	}

	public function ack(Immo_MQ_Message $message)
	{
		//удаляем из очереди
		$messageId = $message->getMQMessageId();
		$this->_connection->acknowledge($messageId);
	}

	public function nack(Immo_MQ_Message $message)
	{
		//оставляем в очереди
		$messageId = $message->getMQMessageId();
		$this->_connection->nacknowledge($messageId);
	}

	public function nacknowledge($messageId)
	{
		//удаляем из очереди
		$this->_connection->acknowledge($messageId);
	}

	public function get()
	{
		//обязательно подписываемся в начале и отписываемся в конце
		$msg = $this->_connection->readFrame();
		if(!empty($msg))
		{
			if($this->validate($msg->body))
			{
				//создаем объект message на основе валидного XML
				$message = new Immo_MQ_Message($msg->body);
				$message->setMQMessageId($msg->headers['message-id']);
				return $message;
			}
			else
			{
				//удаляем сообщение и выдаем сообщение о невалидности XML
				$this->nacknowledge($msg->headers['message-id']);
				throw new Exception('ERROR: XML not validated');
			}
		}
		else
			return null;
	}

	public function getWithoutValidate()
	{
		//обязательно подписываемся в начале и отписываемся в конце
		$msg = $this->_connection->readFrame();
		if(!empty($msg))
		{
			//пытаемся создать message на основе невалидированного сообщения
			$message = new Immo_MQ_Message($msg->body);
			$message->setMQMessageId($msg->headers['message-id']);
			return $message;
		}
		else
			return null;
	}

	public function subscribe()
	{
		//подписываемся на очередь
		$this->_connection->subscribe($this->_channelName);
	}

	public function unsubscribe()
	{
		//отписываемся
		$this->_connection->unsubscribe($this->_channelName);
	}
}