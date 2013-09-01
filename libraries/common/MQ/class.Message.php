<?php

/**
 * Immo_MQ_Message
 * Класс для работы с XML как с сообщениями
 */
class Immo_MQ_Message
{
	protected $CorrelationID = null;
	protected $TypeID = null;
	protected $FormatIndicator = null;
	protected $ReturnAddress = null;
	protected $ExpirationDate = null;
	protected $SequenceTotalCount = null;
	protected $SequencePosition = null;
	protected $AdditionalHeaders = array();
	protected $MQMessageData = null;

	protected $MQMessageId = null;
	protected $MQTransactionId = null;
	protected $DeliveryInfo = null;

	protected $headers = array();

	protected $params = array();


	public function __construct($xml = null)
	{
		if ($xml) $this->parseXmlMessage($xml);
	}


	////////////////////////////////////////////////////////GETTERS////////////////////////////////////////////////////////
	public function getCorrelationID()
	{
		return $this->CorrelationID;
	}

	public function getTypeID()
	{
		return $this->TypeID;
	}

	public function getFormatIndicator()
	{
		return $this->FormatIndicator;
	}

	public function getReturnAddress()
	{
		return $this->ReturnAddress;
	}

	public function getExpirationDate()
	{
		return $this->ExpirationDate;
	}

	public function getSequenceTotalCount()
	{
		return $this->SequenceTotalCount;
	}

	public function getSequencePosition()
	{
		return $this->SequencePosition;
	}

	public function getAdditionalHeaders()
	{
		return $this->AdditionalHeaders;
	}

	public function getMQMessageData()
	{
		return $this->MQMessageData;
	}

	public function getMQMessageId()
	{
		return $this->MQMessageId;
	}

	public function getMQTransactionId()
	{
		return $this->MQTransactionId;
	}

	public function getDeliveryInfo($name = null)
	{
		if ($name === null) return $this->DeliveryInfo;

		return isset($this->DeliveryInfo[$name]) ? $this->DeliveryInfo[$name] : null;
	}

	public function getHeader($name)
	{
		return isset($this->headers[$name]) ? $this->headers[$name] : null;
	}

	public function getHeaders()
	{
		return $this->headers;
	}

	public function getParam($name)
	{
		return isset($this->params[$name]) ? $this->params[$name] : null;
	}


	////////////////////////////////////////////////////////SETTERS////////////////////////////////////////////////////////
	public function setDeliveryInfo($value)
	{
		$this->DeliveryInfo = $value;

		return $this;
	}

	public function setCorrelationID($value)
	{
		$this->CorrelationID = $value;

		return $this;
	}

	public function setTypeID($value)
	{
		$this->TypeID = $value;

		return $this;
	}

	public function setFormatIndicator($value)
	{
		$this->FormatIndicator = $value;

		return $this;
	}

	public function setReturnAddress($value)
	{
		$this->ReturnAddress = $value;

		return $this;
	}

	public function setExpirationDate($value)
	{
		$this->ExpirationDate = $value;

		return $this;
	}

	public function setSequenceTotalCount($value)
	{
		$this->SequenceTotalCount = $value;

		return $this;
	}

	public function setSequencePosition($value)
	{
		$this->SequencePosition = $value;

		return $this;
	}

	public function setAdditionalHeaders($value)
	{
		$this->AdditionalHeaders = $value;

		return $this;
	}

	public function setMQMessageData($value)
	{
		$this->MQMessageData = $value;

		return $this;
	}

	public function setMQMessageId($value)
	{
		$this->MQMessageId = $value;

		return $this;
	}

	public function setMQTransactionId($value)
	{
		$this->MQTransactionId = $value;

		return $this;
	}

	public function setHeader($name, $value)
	{
		$this->headers[$name] = $value;

		return $this;
	}

	public function setHeaders($values)
	{
		$this->headers = $values;

		return $this;
	}

	public function setParam($name, $value)
	{
		$this->params[$name] = $value;

		return $this;
	}


	////////////////////////////////////////////////////////LOGIC////////////////////////////////////////////////////////
	public function parseXmlMessage($xml)
	{
		$xml_node = new SimpleXMLElement($xml);

		$fields = array('CorrelationID', 'TypeID', 'FormatIndicator', 'ReturnAddress', 'ExpirationDate', 'SequenceTotalCount', 'SequencePosition', 'AdditionalHeaders', 'MQMessageData');

		foreach ($fields as $f)
			$this->{$f} = $this->xmlValue($xml_node, $f);

		return $this;
	}


	public function getAsXml()
	{
		$xml = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><MQMessage></MQMessage>");

		if ($this->CorrelationID) $xml->addChild('CorrelationID', $this->CorrelationID);
		if ($this->TypeID) $xml->addChild('TypeID', $this->TypeID);
		if ($this->FormatIndicator) $xml->addChild('FormatIndicator', $this->FormatIndicator);
		if ($this->ReturnAddress) $xml->addChild('ReturnAddress', $this->ReturnAddress);
		if ($this->ExpirationDate) $xml->addChild('ExpirationDate', $this->ExpirationDate);
		if ($this->SequenceTotalCount) $xml->addChild('SequenceTotalCount', $this->SequenceTotalCount);
		if ($this->SequencePosition) $xml->addChild('SequencePosition', $this->SequencePosition);

		if ($this->AdditionalHeaders)
			$this->xmlAddChild($xml, 'AdditionalHeaders', $this->AdditionalHeaders);

		$this->xmlAddChild($xml, 'MQMessageData', $this->MQMessageData);

		return $xml->asXML();
	}


	protected function xmlAddChild($xml, $name, $data)
	{
		if (!is_array($data))
		{
			$xml->addChild($name, $data);

			return $this;
		}

		$current = null;

		foreach ($data as $key => $value)
		{
			if (is_integer($key))
			{
				$this->xmlAddChild($xml, $name, $value, $key);
			}
			else
			{
				if (!$current)
					$current = $xml->addChild($name);

				$this->xmlAddChild($current, $key, $value);
			}
		}

		return $this;
	}

	protected function xmlValue($xml, $name)
	{
		if (!isset($xml->{$name}[0])) return null;

		$xml_node = $xml->{$name}[0];

		if (!$xml_node->children()) return (string) $xml_node;

		$data = array();

		$this->xmlArrayValue($xml_node, $data);

		return $data;
	}

	protected function xmlArrayValue($xml_node, &$data)
	{
		$parts = array();
		$single = array();

		foreach ($xml_node as $key => $value)
		{
			if ($value->children())
			{
				if (isset($parts[$key]))
				{
					$array_key = ++$parts[$key];

					if ($single[$key])
					{
						$data[$key] = array($data[$key]);
						$single[$key] = false;
					}

					$data[$key][$array_key] = array();

					$this->xmlArrayValue($value, $data[$key][$array_key]);
				}
				else
				{
					$parts[$key] = 0;
					$single[$key] = true;

					$data[$key] = array();

					$this->xmlArrayValue($value, $data[$key]);
				}
			}
			else
				$data[$key] = htmlspecialchars((string) $value);
		}

		return $this;
	}
}
