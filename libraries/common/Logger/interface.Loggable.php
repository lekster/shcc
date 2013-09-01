<?php
/**
* Интерфейс механизма логирования
*/

interface Immo_MobileCommerce_Loggable
{   
	const Debug = 0;
    const Info  = 1;
    const Warn  = 2;
    const Error = 3;
    const Fatal = 4;
    
    /**
    * Инициализировать механизм логирования
    * 
    * @param string $filename
    * @param integer $level
    * @param integer $tracedepth
    * @return Immo_Runiverse_Loggable
    */
    public function __construct($filename = false, $level = false, $tracedepth = false);
    
    /**
    * Установить уровень логирования
    * 
    * @param integer $logLevel
    */
    public function setLevel($logLevel);
    
    /**
    * Добавить информацию в лог уровня DEBUG
    * 
    * @param string $message
    * @param obj $object
    */
    public function debug($message, $object = false);   
     
    /**
    * Добавить информацию в лог уровня INFO
    * 
    * @param string $message
    * @param obj $object
    */
    public function info($message, $object = false);
    
    /**
    * Добавить информацию в лог уровня WARN
    * 
    * @param string $message
    * @param obj $object
    */
    public function warn($message, $object = false);    
    
    /**
    * Добавить информацию в лог уровня ERROR
    * 
    * @param string $message
    * @param obj $object
    */
    public function error($message, $object = false);
    
    /**
    * Добавить информацию в лог уровня FATAL
    * 
    * @param string $message
    * @param obj $object
    */
    public function fatal($message, $object = false);
}
