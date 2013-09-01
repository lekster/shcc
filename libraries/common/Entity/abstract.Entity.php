<?php
/**
 * Абстрактный класс сущности
 * 
 * @copyright Inform-Mobil
 * @author Vadim Kurochkin <kurochkin@immo.ru>
 */
abstract class Immo_MobileCommerce_Entity
{
	protected $_logger;
	protected $_data = array();

	/**
	 * Конструктор класса. Устанавливает свойства объекта
	 *
	 * @param array $params
	 * @return void
	 */
	public function __construct($params = array())
    {
		$this->_logger = Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger();
        $this->init($params);
	}
    
    public function init($params)
    {
		if (is_array($params))
        {
			foreach ($params as $key => $val)
            {
				$this->_data[$key] = $val;
			}
		}
	}
    
    public function toArray()
    {
        return $this->_data;
    }
}
