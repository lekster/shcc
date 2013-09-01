<?php
/**
 * Класс исключения
 * 
 * @copyright Inform-Mobil
 * @author Vadim Kurochkin <kurochkin@immo.ru>
 */
class Immo_MobileCommerce_Exception extends Exception
{
	protected $_codeToken;
	
	public function __construct($message, $codeToken = '')
	{
		$this->_codeToken = $codeToken;
		parent::__construct($message);
	}
	
	public function getCodeToken()
	{
		return $this->_codeToken;
	}
}