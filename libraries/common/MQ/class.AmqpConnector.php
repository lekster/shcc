<?php
/**
 * Immo_MQ_AmqpConnector
 * Класс для работы с брокером сообщений MessageMQ по AMQP протоколу
 */
require_once 'pbr-lib-common/src/MQ/abstract.Connector.php';
require_once 'pbr-lib-common/src/MQ/src/php-amqplib/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;


class Immo_MQ_AmqpConnector extends Immo_MQ_Connector
{
	protected $_connection;

	protected $_vhost = '/';
	protected $_port = 5672;

	public $last_error = null;


	public function __construct($server, $login, $password, $heartbeat, $messageXsd, $dataXsd, $channelName)
	{
		parent::__construct($server, $login, $password, $heartbeat, $messageXsd, $dataXsd, $channelName);

		$this->_connection = new AMQPConnection($this->_server, $this->_port, $this->_login, $this->_password, $this->_vhost);
	}


	public function close()
	{
		$this->_connection->close();
	}

	public function send(Immo_MQ_Message $message)
	{
		$result = false;

		try {
			$msg = new AMQPMessage($message->getAsXml(), array(
				'content_type' => 'text/xml',
				'delivery_mode' => 2,
				'application_headers' => $this->convertArrayToHeaderMessage($message->getHeaders()),
			));

			$channel = $this->_connection->channel(2);

			$channel->basic_publish($msg, $this->_channelName, $message->getParam('routing_key'));

			$channel->close();

			$result = true;
		}
		catch (Exception $e)
		{
			$this->last_error = $e;
		}

		return $result;
	}

	public function ack(Immo_MQ_Message $message)
	{
		$deliveryInfo = $message->getDeliveryInfo();

		$this->_connection->channel(1)->basic_ack($deliveryInfo['delivery_tag']);
	}

	public function nack(Immo_MQ_Message $message)
	{
		$deliveryInfo = $message->getDeliveryInfo();

		$this->_connection->channel(1)->basic_nack($deliveryInfo['delivery_tag']);
	}

	public function get()
	{
		$channel = $this->_connection->channel(1);

		$msg = $channel->basic_get($this->_channelName, false, null);

		if (!$msg) return null;

		if (!$this->validate($msg->body))
		{
			$channel->basic_nack($msg->delivery_info['delivery_tag']);

			throw new Exception('ERROR: XML not validated');
		}

		$message = new Immo_MQ_Message($msg->body);

		$message->setDeliveryInfo($msg->delivery_info);

		if ($msg->has('application_headers'))
			$message->setHeaders($this->convertHeaderMessageToArray($msg->get('application_headers')));

		return $message;
	}

/*
	// Транзакционность пока не работает, то что ниже - не проверено. При send'е канал закрывается, по этому пока это работать не будет.

	public function beginT()
	{
		//создаем уникальный ID транзакции

		$transactionId = uuid_create();

		$this->_connection->channel(1)->tx_select();

		return $transactionId;
	}

	public function commitT($transactionId)
	{
		//комитим указанную транзакцию
		try {
			$this->_connection->channel(1)->tx_commit();
		}
		catch(Exception $e)
		{
			//throw new $e;
			return false;
		}

		return true;
	}

	public function commitTAndClose($transactionId)
	{
		//комитим указанную транзакцию
		$this->_connection->channel(1)->tx_commit();
		//$this->_channel->close();
		//$this->_connection->close();
	}

	public function abortT($transactionId)
	{
		//абортим указанную транзакцию
		$this->_connection->channel(1)->tx_rollback();
	}
*/
	public function beginT() { throw new Exception('Method is not implemented'); }
	public function commitT($transactionId) { throw new Exception('Method is not implemented'); }
	public function commitTAndClose($transactionId) { throw new Exception('Method is not implemented'); }
	public function abortT($transactionId) { throw new Exception('Method is not implemented'); }


	public function getWithoutValidate()
	{
		//обязательно подписываемся в начале и отписываемся в конце
		$msg = $this->_connection->channel(1)->basic_get($this->_channelName, false, null);

		if (!$msg) return null;

		$message = new Immo_MQ_Message($msg->body);

		$message->setDeliveryInfo($msg->delivery_info);

		return $message;
	}


	public function subscribe()
	{
		//подписываемся на очередь
	}

	public function unsubscribe()
	{
		//отписываемся
	}


	public function getMessageCount()
	{
		$res = $this->_connection->channel(1)->queue_declare($this->_channelName, true);

		return isset($res[1]) ? intval($res[1]) : null;
	}

	static public function convertArrayToHeaderMessage($values)
	{
		$result = array();

		foreach ($values as $key => $value)
		{
			$type = null;

			switch (gettype($value))
			{
				case 'boolean': $type = 'I'; $value = intval($value); break;
				case 'string': $type = 'S'; break;
				case 'integer': $type = 'I'; break;
				// case 'decimal': $type = 'D'; break;
				// case 'table': $type = 'T'; break;
				case 'double': $type = 'S'; break;
				// case 'array': $type = 'A'; break;
				default: break;
			}

			if ($type) $result[$key] = array($type, $value);
		}

		return $result;
	}

	static public function convertHeaderMessageToArray($values)
	{
		if (!is_array($values)) return $values;

		$result = array();

		foreach ($values as $key => $value)
		{
			if (is_array($value))
			{
				if ($value[0] === 'A')
				{
					$result[$key] = array();

					foreach ($value[1] as $k => $v)
						$result[$key][$k] = self::convertHeaderMessageToArray($v);
				}
				else
					$result[$key] = $value[1];
			}
			else
				$result[$key] = $value;
		}

		return $result;
	}
}
