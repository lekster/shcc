<?php

require_once 'libraries/internal/php5/mobile-commerce/common/src/class.Exception.php';
require_once 'libraries/internal/php5/mobile-commerce/common/src/ServiceLocator/class.ServiceLocator.php';

/**
 * Класс Immo_MobileCommerce_Request_MobiDengi_InternetShop
 *
 * @copyright Inform-Mobil
 * @author Vadim Kurochkin <kurochkin@immo.ru>
 */
 
class Immo_MobileCommerce_Request_Inplat_InternetShop
{
	protected $_orderId;
	protected $_logger;
        protected $_payerPhone;
	
    public function __construct($userParams)
    {
		$this->_logger = Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger();
	
        parse_str($userParams, $userParamsArr);
		
		if (empty($userParamsArr['orderID']))
		{
			throw new Immo_MobileCommerce_Exception('EMPTY orderID PARAM');
		}
		
		$this->_orderId = $userParamsArr['orderID'];
                $this->_payerPhone = $userParamsArr['payerPhone'];
    }
	
	public function getOrderId()
	{
		return $this->_orderId;
	}
        
        public function getPayerPhone()
	{
		return $this->_payerPhone;
	}
}
