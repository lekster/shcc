<?php

class PaymentContractResponse
{
	protected $paymentUUID;
	protected $sum;
	protected $paymentDelay;
	protected $textProfileId;
	protected $additionalParams;
	protected $errorCode;
	protected $errorDescription;

	public function setPaymentUUID($val)
	{
		$this->paymentUUID = $val;
	}

	public function getPaymentUUID()
	{
		return $this->paymentUUID;
	}

	public function setSum($val)
	{
		$this->sum = $val;
	}

	public function getSum()
	{
		return $this->sum;
	}

	public function setPaymentDelay($val)
	{
		$this->paymentDelay = $val;
	}

	public function getPaymentDelay()
	{
		return $this->paymentDelay;
	}


	public function setTextProfileId($val)
	{
		$this->textProfileId = $val;
	}

	public function getTextProfileId()
	{
		return $this->textProfileId;
	}

	public function setErrorCode($val)
	{
		$this->errorCode = $val;
	}

	public function getErrorCode()
	{
		return $this->errorCode;
	}

	public function setErrorDescription($val)
	{
		$this->errorDescription = $val;
	}

	public function getErrorDescription()
	{
		return $this->errorDescription;
	}

	public function setAdditionalParams($val)
	{
		$this->additionalParams = $val;
	}

	public function getAdditionalParams()
	{
		return $this->additionalParams;
	}
	
	public function getAsArray()
	{
		$arr = array
		(
		"PaymentContractResponse" => array(
			"PaymentUUID" => $this->getPaymentUUID(),
			"Sum" => $this->getSum(),
			"PaymentDelay" => $this->getPaymentDelay(),
			"TextProfileId" => $this->getTextProfileId(),
		));

		if ($this->getAdditionalParams() != null) $arr["PaymentContractResponse"]["AdditionalParams"] = $this->getAdditionalParams();
		return $arr;
	}

	public static function parseFromArray($data)
	{
		if (is_array($data['PaymentContractResponse']))
		{
			$val = $data['PaymentContractResponse'];
			$result = new PaymentContractResponse();
			$result->setPaymentUUID(@$val['PaymentUUID']);
			$result->setSum(@$val['Sum']);
			$result->setPaymentDelay(@$val['PaymentDelay']);
			$result->setTextProfileId(@$val['TextProfileId']);
			$result->setAdditionalParams(@$val['AdditionalParams']);
			$result->setErrorCode(isset($val['Error']['Code']) ? $val['Error']['Code'] : null );
			$result->setErrorDescription(isset($val['Error']['Description']) ? $val['Error']['Description'] : null);

			return $result;
		}
		return null;
	}
}


/*
<?xml version="1.0" encoding="utf-8"?>
<xs:schema
  xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="PaymentContractResponse" type="PaymentContractResponse"/>
  <xs:complexType name="PaymentContractResponse">
      <xs:sequence>
        <xs:element name="PaymentUUID" type="xs:string"/>
        <xs:element name="Sum" type="xs:float"/>
        <xs:element name="PaymentDelay" type="xs:decimal"/>
        <xs:element name="TextProfileId" type="xs:decimal" minOccurs="0"/>
        <xs:element name="AdditionalParams" type="xs:string" minOccurs="0"/>
        <xs:element name="Error" type="Error_headers" minOccurs="0"/>
      </xs:sequence>
  </xs:complexType>
  
  <xs:complexType name="Error_headers">
        <xs:sequence>
          <xs:element name="Code" type="xs:decimal"/>
          <xs:element name="Description" type="xs:string"/>
        </xs:sequence>
  </xs:complexType>

</xs:schema>







*/