<?php

class PaymentCancellationRequest
{
	protected $paymentUUID;
	protected $cancellationTime;
	protected $reasonCode;
	protected $reasonDescription;
	protected $additionalParams;
	protected $paymentForeignId;
	

	public function setPaymentUUID($val)
	{
		$this->paymentUUID = $val;
	}

	public function getPaymentUUID()
	{
		return $this->paymentUUID;
	}

	public function setCancellationTime($val)
	{
		$time = strtotime($val);
		if ($time)
		{
			$this->cancellationTime = date("c", $time);
		}
		else
		{
			$this->cancellationTime = date("c");
		}
	}

	public function getCancellationTime()
	{
		if ($this->cancellationTime == null) return date("c");
		return $this->cancellationTime;
	}

	public function setReasonCode($val)
	{
		$this->reasonCode = $val;
	}

	public function getReasonCode()
	{
		return $this->reasonCode;
	}

	public function setReasonDescription($val)
	{
		$this->reasonDescription = $val;
	}

	public function getReasonDescription()
	{
		return $this->reasonDescription;
	}

	public function setAdditionalParams($val)
	{
		$this->additionalParams = $val;
	}

	public function getAdditionalParams()
	{
		return $this->additionalParams;
	}

	public function setPaymentForeignId($val)
	{
		$this->paymentForeignId = $val;
	}

	public function getPaymentForeignId()
	{
		return $this->paymentForeignId;
	}

	public function getAsArray()
	{
		$arr = array
		(
		"PaymentCancellationRequest" => array(
			"PaymentUUID" => $this->getPaymentUUID(),
			"CancellationTime" => $this->getCancellationTime(),
			"ReasonCode" => $this->getReasonCode(),
			"ReasonDescription" => $this->getReasonDescription(),
		));

		if ($this->getAdditionalParams() != null) $arr["PaymentCancellationRequest"]["AdditionalParams"] = $this->getAdditionalParams();
		if ($this->getPaymentForeignId() != null) $arr["PaymentCancellationRequest"]["PaymentForeignId"] = $this->getPaymentForeignId();
		return $arr;
	}
}


/*
<?xml version="1.0" encoding="utf-8"?>
<xs:schema
  xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="PaymentCancellationRequest" type="PaymentCancellationRequest"/>
  <xs:complexType name="PaymentCancellationRequest">
      <xs:sequence>
        <xs:element name="PaymentUUID" type="xs:string"/>
        <xs:element name="CancellationTime" type="xs:dateTime"/>
        <xs:element name="ReasonCode" type="xs:decimal"/>
        <xs:element name="ReasonDescription" type="xs:string"/>
        <xs:element name="AdditionalParams" type="xs:string" minOccurs="0"/>
        <xs:element name="PaymentForeignId" type="xs:string" minOccurs="0"/>
      </xs:sequence>
  </xs:complexType>

</xs:schema>

<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<MQMessage>
    <CorrelationID>f8b0b1a8-2ac0-47dd-82c2-f62e85cc4e7e</CorrelationID>
    <MQMessageData><PaymentCancellationRequest>
    <PaymentUUID>5c7a8e36-2a77-4884-8e4b-64e14cc64bd8</PaymentUUID>
    <CancellationTime>2015-02-02T11:27:31.942+03:00</CancellationTime>
    <ReasonCode>-1</ReasonCode>
    <PaymentForeignId></PaymentForeignId>
</PaymentCancellationRequest></MQMessageData>
</MQMessage>

<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<MQMessage>
    <CorrelationID>0e06e270-2140-4358-a3e5-90b4e6a352cc</CorrelationID>
    <MQMessageData><PaymentCancellationRequest>
    <PaymentUUID>d06511c5-bfe9-44e4-8c33-50ff0c1ab686</PaymentUUID>
    <CancellationTime>2015-02-02T13:34:04.032+03:00</CancellationTime>
    <ReasonCode>-1</ReasonCode>
    <ReasonDescription>abonent MC payment method disable</ReasonDescription>
    <PaymentForeignId></PaymentForeignId>
</PaymentCancellationRequest></MQMessageData>
</MQMessage>

*/