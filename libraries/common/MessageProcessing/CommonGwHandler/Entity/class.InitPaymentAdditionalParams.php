<?php

class InitPaymentAdditionalParams
{
	private $routeParams;
	private $serviceParams;
	private $productParams;
	private $processingParams;
	private $transactionParams;
	private $operatorParams;
	private $transactionAdditionalParams;


	public function setRouteParams($val)
	{
		$this->routeParams = $val;
	}

	public function getRouteParams()
	{
		return $this->routeParams;
	}

	public function setServiceParams($val)
	{
		$this->serviceParams = $val;
	}

	public function getServiceParams()
	{
		return $this->serviceParams;
	}

	public function setProductParams($val)
	{
		$this->productParams = $val;
	}

	public function getProductParams()
	{
		return $this->productParams;
	}

	public function setProcessingParams($val)
	{
		$this->processingParams = $val;
	}

	public function getProcessingParams()
	{
		return $this->processingParams;
	}

	public function setTransactionParams($val)
	{
		$this->transactionParams = $val;
	}

	public function getTransactionParams()
	{
		return $this->transactionParams;
	}

	public function setOperatorParams($val)
	{
		$this->operatorParams = $val;
	}

	public function getOperatorParams()
	{
		return $this->operatorParams;
	}

	public function setTransactionAdditionalParams($val)
	{
		$this->transactionAdditionalParams = $val;
	}

	public function getTransactionAdditionalParams()
	{
		return $this->transactionAdditionalParams;
	}

	public function parseStr($str)
	{
		$strDec = htmlspecialchars_decode($str);
		$arr = json_decode($strDec, true);
		if (is_array($arr))
		{
			$result = new InitPaymentAdditionalParams();
			$result->setRouteParams(@$arr['RouteParams']);
			$result->setServiceParams(@$arr['ServiceParams']);
			$result->setProductParams(@$arr['ProductParams']);
			$result->setProcessingParams(@$arr['ProcessingParams']);
			$result->setTransactionParams(@$arr['TransactionParams']);
			$result->setOperatorParams(@$arr['OperatorParams']);
			$result->setTransactionAdditionalParams(@$arr['TransactionAdditionalParams']);

			return $result;

		}
		return null;
	}


}