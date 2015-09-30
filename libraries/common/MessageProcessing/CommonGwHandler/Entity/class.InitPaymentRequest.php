<?php

class InitPaymentRequest
{
	protected $paymentUUID;
	protected $sumForProcessing;
	protected $sumForAbonent;
	protected $serviceNumber;
	protected $msisdn;
	protected $serviceName;
	protected $paymentDescription;
	protected $accountID;
	protected $textProfileId;
	protected $params;


	public function setPaymentUUID($val)
	{
		$this->paymentUUID = $val;
	}

	public function getPaymentUUID()
	{
		return $this->paymentUUID;
	}

	public function setSumForProcessing($val)
	{
		$this->sumForProcessing = $val;
	}

	public function getSumForProcessing()
	{
		return $this->sumForProcessing;
	}

	public function setSumForAbonent($val)
	{
		$this->sumForAbonent = $val;
	}

	public function getSumForAbonent()
	{
		return $this->sumForAbonent;
	}

	public function setServiceNumber($val)
	{
		$this->serviceNumber = $val;
	}

	public function getServiceNumber()
	{
		return $this->serviceNumber;
	}

	public function setMsisdn($val)
	{
		$this->msisdn = $val;
	}

	public function getMsisdn()
	{
		return $this->msisdn;
	}

	public function setServiceName($val)
	{
		$this->serviceName = $val;
	}

	public function getServiceName()
	{
		return $this->serviceName;
	}

	public function setPaymentDescription($val)
	{
		$this->paymentDescription = $val;
	}

	public function getPaymentDescription()
	{
		return $this->paymentDescription;
	}

	public function setAccountID($val)
	{
		$this->accountID = $val;
	}

	public function getAccountID()
	{
		return $this->accountID;
	}

	public function setTextProfileId($val)
	{
		$this->textProfileId = $val;
	}

	public function getTextProfileId()
	{
		return $this->textProfileId;
	}

	public function setAdditionalParams($val)
	{
		$this->params = $val;
	}

	public function getAdditionalParams()
	{
		return $this->params;
	}

	public static function parseFromArray($data)
	{
		if (is_array($data['InitPaymentRequest']))
		{
			$val = $data['InitPaymentRequest'];
			$result = new InitPaymentRequest();
			$result->setPaymentUUID(@$val['PaymentUUID']);
			$result->setSumForProcessing(@$val['SumForProcessing']);
			$result->setSumForAbonent(@$val['SumForAbonent']);
			$result->setServiceNumber(@$val['ServiceNumber']);
			$result->setMsisdn(@$val['Msisdn']);
			$result->setServiceName(@$val['ServiceName']);
			$result->setPaymentDescription(@$val['PaymentDescription']);
			$result->setAccountID(@$val['AccountID']);
			$result->setTextProfileId(@$val['TextProfileId']);
			$result->setAdditionalParams(@$val['AdditionalParams']);

			return $result;
		}
		return null;
	}

}

/*
'PaymentUUID' => string 'c8a8ea53-6106-4289-bc87-780dac3d3c55' (length=36)
          'SumForProcessing' => string '0' (length=1)
          'SumForAbonent' => string '10000' (length=5)
          'ServiceNumber' => string '' (length=0)
          'Msisdn' => string '79261206295' (length=11)
          'ServiceName' => string 'fAil' (length=4)
          'PaymentDescription' => string '' (length=0)
          'AccountID' => string '' (length=0)
          'TextProfileId' => string '2' (length=1)
          'AdditionalParams'
*/