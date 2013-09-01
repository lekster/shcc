<?php

/**
 * Класс Immo_MobileCommerce_Event_Handler
 * 
 */
abstract class Immo_MobileCommerce_Event
{
	protected $_config;
	protected $_logger;
	
	public function __construct()
	{
		$serviceLocator = Immo_MobileCommerce_ServiceLocator::getInstance();
		$this->_config = $serviceLocator->getConfig();
		$this->_logger = $serviceLocator->getLogger();
	}
	
	abstract public function process($params);
}
