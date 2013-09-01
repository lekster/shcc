<?php
namespace aop;

require_once __DIR__ . '/Aop.php';

class AopExt extends Aop
{
    protected $config;
       
    public function __construct($config)
    {
        parent::__construct();
        $this->config = $config;
    }

    public function setByConfArray($confArr)
    {
        foreach ($confArr as $joinPoint => $aspectIocName)
        {
            $aspectObj = $this->config->getIOCObject($aspectIocName);
            $this->add($joinPoint, $aspectObj);
        }
    }
}
