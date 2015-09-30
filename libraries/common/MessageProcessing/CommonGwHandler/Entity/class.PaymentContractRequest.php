<?php

class PaymentContractRequest
{
	protected $paymentUUID;
	protected $serviceName;
	protected $processingName;
	protected $sum;
	protected $msisdn;
	protected $paymentDescription;
	protected $accountID;
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


	public function setServiceName($val)
	{
		$this->serviceName = $val;
	}

	public function getServiceName()
	{
		return $this->serviceName;
	}


	public function setProcessingName($val)
	{
		$this->processingName = $val;
	}

	public function getProcessingName()
	{
		return $this->processingName;
	}


	public function setSum($val)
	{
		$this->sum = $val;
	}

	public function getSum()
	{
		return $this->sum;
	}


	public function setMsisdn($val)
	{
		$this->msisdn = $val;
	}

	public function getMsisdn()
	{
		return $this->msisdn;
	}

	public function setPaymentDescription($val)
	{
		$this->paymentDescription = $val;
	}

	public function getPaymentDescription()
	{
		return $this->paymentDescription;
	}

	public function setAccountId($val)
	{
		$this->accountID = $val;
	}

	public function getAccountId()
	{
		return $this->accountID;
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
		"PaymentContractRequest" => array(
			"PaymentUUID" => $this->getPaymentUUID(),
			"ServiceName" => $this->getServiceName(),
			"ProcessingName" => $this->getProcessingName(),
			"Sum" => $this->getSum(),
			"Msisdn" => $this->getMsisdn(),
			"PaymentDescription" => $this->getPaymentDescription(),
			"AccountID" => $this->getAccountId(),
		));

		if ($this->getAdditionalParams() != null) $arr["PaymentContractRequest"]["AdditionalParams"] = $this->getAdditionalParams();
		if ($this->getPaymentForeignId() != null) $arr["PaymentContractRequest"]["PaymentForeignId"] = $this->getPaymentForeignId();
		return $arr;
	}
}

/*
<?xml version="1.0" encoding="utf-8"?>
<xs:schema
  xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="PaymentContractRequest" type="PaymentContractRequest"/>
  <xs:complexType name="PaymentContractRequest">
      <xs:sequence>
        <xs:element name="PaymentUUID" type="xs:string"/>
        <xs:element name="ServiceName" type="xs:string"/>
        <xs:element name="ProcessingName" type="xs:string"/>
        <xs:element name="Sum" type="xs:float"/>
        <xs:element name="Msisdn" type="xs:decimal"/>
        <xs:element name="PaymentDescription" type="xs:string"/>
        <xs:element name="AccountID" type="xs:string"/>
        <xs:element name="AdditionalParams" type="xs:string" minOccurs="0"/>
        <xs:element name="PaymentForeignId" type="xs:string" minOccurs="0"/>
      </xs:sequence>
  </xs:complexType>

</xs:schema>

*/