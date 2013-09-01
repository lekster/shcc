<?php

require_once 'pbr-lib-common/src/Event/Handler/abstract.Event.php';

/**
 * Класс Immo_MobileCommerce_Event
 * 
 */
class PBR_LIB_COMMON_EventHandler
{
	protected static $_instance;
	protected $_config;
	protected $_logger;
	protected $_facade;
	
	public static function getInstance()
	{
		if (!self::$_instance) { self::$_instance = new self(); }
		return self::$_instance;
	}
	
	public function __construct()
	{
		$serviceLocator = Immo_MobileCommerce_ServiceLocator::getInstance();
		$this->_config = $serviceLocator->getConfig();
		$this->_logger = $serviceLocator->getLogger();
	}
	
    public function raise($name, $params = array())
    {
		$eventClassNames = $this->_config->get($name, 'Events');
		$this->_logger->debug('RAISING EVENT '.$name.' WITH PARAMS', $params);
		$this->_logger->debug('FOUND EVENTS', $eventClassNames);
		if (empty($eventClassNames) || !is_array($eventClassNames)) { return false; }
		foreach($eventClassNames as $eventClassName)
		{
                        try
                        {
                            $className = $this->_config->getImplementation($eventClassName);
                        }
                        catch (Exception $e)
                        {
                            $this->_logger->fatal($e->getMessage());
                            throw $e;
                        }
                        $event = new $className();
			
			if (!($event instanceof Immo_MobileCommerce_Event))
			{
				throw new Exception('Event handler '.get_class($event).' must implements Event_Handler');
			}
			
			$event->process($params);
		}
		
		return true;
    }
}
