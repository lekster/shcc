<?php

//require_once 'libraries/internal/php5/zhkh/common/src/Entity/abstract.Entity.php';

/**
 * Класс Immo_MobileCommerce_SojuzTeleCom_Request
 *
 * @copyright Inform-Mobil
 * @author Vadim Kurochkin <kurochkin@immo.ru>
 */
abstract class Immo_MobileCommerce_SojuzTeleCom_Request_Check //extends Immo_MobileCommerce_Entity
{
    protected $_id;
    protected $_phone;
    // protected $_result;
    protected $_control;
    protected $_cmd;
    
    public function __contruct($xmlStr)
    {
        $this->init($xmlStr);
    }
    
    public function init($xmlStr)
    {
        $xml = new SimpleXMLElement($xmlStr);
        $this->_id      = $xml->id;
        $this->_phone   = $xml->phone;
        // $this->_result  = $xml->result;
        $this->_control = $xml->control;
        $this->_cmd     = $xml->cmd;
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function getPhone()
    {
        return $this->_phone;
    }
    
    public function getResult()
    {
        return $this->_result;
    }
    
    public function getControl()
    {
        return $this->_control;
    }
    
    public function getCmd()
    {
        return $this->_cmd;
    }
}
