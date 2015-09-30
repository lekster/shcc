<?php

class PaymentAuthorizationRequest
{
	protected $paymentUUID;
	protected $authorizationTime;
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

	public function setAuthorizationTime($val)
	{
		$time = strtotime($val);
		if ($time)
		{
			$this->authorizationTime = date("c", $time);
		}
		else
		{
			$this->authorizationTime = date("c");
		}
	}

	public function getAuthorizationTime()
	{
		if ($this->authorizationTime == null) return date("c");
		return $this->authorizationTime;
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
		"PaymentAuthorizationRequest" => array(
			"PaymentUUID" => $this->getPaymentUUID(),
			"AuthorizationTime" => $this->getAuthorizationTime(),
		));

		if ($this->getAdditionalParams() != null) $arr["PaymentAuthorizationRequest"]["AdditionalParams"] = $this->getAdditionalParams();
		if ($this->getPaymentForeignId() != null) $arr["PaymentAuthorizationRequest"]["PaymentForeignId"] = $this->getPaymentForeignId();
		return $arr;
	}
}

/*

<?xml version="1.0" encoding="utf-8"?>
<xs:schema
  xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="PaymentAuthorizationRequest" type="PaymentAuthorizationRequest"/>
  <xs:complexType name="PaymentAuthorizationRequest">
      <xs:sequence>
        <xs:element name="PaymentUUID" type="xs:string"/>
        <xs:element name="AuthorizationTime" type="xs:dateTime"/>
        <xs:element name="AdditionalParams" type="xs:string" minOccurs="0"/>
        <xs:element name="PaymentForeignId" type="xs:string" minOccurs="0"/>
      </xs:sequence>
  </xs:complexType>
</xs:schema>


*/