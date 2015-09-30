<?php

class InitPaymentResponse
{
	
	private $paymentUUID;
	private $paymentTime;
	private $paymentResultCode;
	private $paymentResultDescription;
	private $additionalParams;
	//private ErrorCode;
	//private ErrorDescription;
	private $paymentForeignId;


	public function setPaymentUUID($val)
	{
		$this->paymentUUID = $val;
	}

	public function getPaymentUUID()
	{
		return $this->paymentUUID;
	}

	public function setPaymentTime($val)
	{
		$time = strtotime($val);
		if ($time)
		{
			$this->paymentTime = date("c", $time);
		}
		else
		{
			$this->paymentTime = date("c");
		}
	}

	public function getPaymentTime()
	{
		if ($this->paymentTime == null) return date("c");
		return $this->paymentTime;
	}

	public function setPaymentResultCode($val)
	{
		$this->paymentResultCode = $val;
	}

	public function getPaymentResultCode()
	{
		return $this->paymentResultCode;
	}

	public function setPaymentResultDescription($val)
	{
		$this->paymentResultDescription = $val;
	}

	public function getPaymentResultDescription()
	{
		return $this->paymentResultDescription;
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
		"InitPaymentResponse" => array(
			"PaymentUUID" => $this->getPaymentUUID(),
			"PaymentTime" => $this->getPaymentTime(),
			"PaymentResultCode" => $this->getPaymentResultCode(),
			"PaymentResultDescription" => $this->getPaymentResultDescription(),
		));

		if ($this->getAdditionalParams() != null) $arr["InitPaymentResponse"]["AdditionalParams"] = $this->getAdditionalParams();
		if ($this->getPaymentForeignId() != null) $arr["InitPaymentResponse"]["PaymentForeignId"] = $this->getPaymentForeignId();
		return $arr;
	}

}


/*

<xs:complexType name="InitPaymentResponse">
      <xs:sequence>
        <xs:element name="PaymentUUID" type="xs:string"/>
        <xs:element name="PaymentTime" type="xs:dateTime"/>
        <xs:element name="PaymentResultCode" type="xs:decimal"/>
        <xs:element name="PaymentResultDescription" type="xs:string"/>
        <xs:element name="AdditionalParams" type="xs:string" minOccurs="0"/>
        <xs:element name="Error" type="Error_headers" minOccurs="0"/>
        <xs:element name="PaymentForeignId" type="xs:string" minOccurs="0"/>
      </xs:sequence>
  </xs:complexType>

  <xs:complexType name="Error_headers">
        <xs:sequence>
          <xs:element name="Code" type="xs:decimal"/>
          <xs:element name="Description" type="xs:string"/>
        </xs:sequence>
  </xs:complexType>

*/