<?php

require_once 'libraries/internal/php5/mobile-commerce/common/src/class.Exception.php';
require_once 'libraries/internal/php5/mobile-commerce/common/src/ServiceLocator/class.ServiceLocator.php';

/**
 * ����� Immo_MobileCommerce_Request_MobiDengi_Billing
 *
 * @copyright Inform-Mobil
 * @author Vadim Kurochkin <kurochkin@immo.ru>
 */
 
class Immo_MobileCommerce_Request_Inplat_Billing
{
	protected $_account;
	protected $_sum;
	protected $_payerPhone;
	protected $_logger;
	
    public function __construct($userParams)
    {
		$this->_logger = Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger();
	
        parse_str($userParams, $userParamsArr);
		
		if (empty($userParamsArr['account']))
		{
			throw new Immo_MobileCommerce_Exception('EMPTY account PARAM');
		}
		
		if (empty($userParamsArr['sum']))
		{
			throw new Immo_MobileCommerce_Exception('EMPTY sum PARAM');
		}
		
		if (empty($userParamsArr['customerNumber']))
		{
			throw new Immo_MobileCommerce_Exception('EMPTY payerPhone PARAM');
		}
		
		$this->_account = $userParamsArr['account'];
		$this->_sum = $userParamsArr['sum'];
		$this->_payerPhone = $userParamsArr['customerNumber'];
    }
	
	public function getSum()
	{
		return $this->_sum;
	}
	
	public function getAccount()
	{
		return $this->_account;
	}
	
	public function getPayerPhone()
	{
		return $this->_payerPhone;
	}
}
