<?php

require_once ("libraries/common/PhpConf/Reader.php");

class Immo_MobileCommerce_Config extends PhpConf_Reader
{
    protected static $_instance;
    protected $_iocContainer = array();

    public function __construct($filename)
    {
        if (is_readable($filename))
			parent::__construct($filename);
    }
    
    public static function getInstance($filename)
    {
        if (!self::$_instance)
        {
            if (!$filename)
            {
                throw new Exception('Missing params. Input: '.var_export(func_get_args(), true));
            }
            
            if (file_exists($filename))
            {
                self::$_instance = new self($filename);
            } else {
                throw new Exception('Config file is not exists: '.var_export($filename, true));
            }
        }
        
        return self::$_instance;
    }
    
    public function toArray()
    {
        return $this->data;
    }
    
    public function getSection($sectionName)
    {
        if (!key_exists($sectionName, $this->data))
        {
            throw new Exception("No '$sectionName' section in config. Allowed sections: ".var_export(array_keys($this->data), true));
        }
        
        return $this->data[$sectionName];
    }
    
    public function getImplementation($class, $scheme = '<default>')
    {
        $implementation = $this->get($class, 'Implementations');
        if (!$implementation)
        {
            throw new Immo_MobileCommerce_Exception("No implementation section '$class' in config.");
        }
        
        $filepath = $implementation[$scheme]['ImplementationClassFilepath'];
        if (!is_file($filepath))
        {
            throw new Immo_MobileCommerce_Exception("File ".var_export($filepath, true)." is not exists or it's not readable.");
        }
        
        $classname = $implementation[$scheme]['ImplementationClassName'];
        if (!$classname)
        {
            throw new Immo_MobileCommerce_Exception("No 'ImplementationClassName' for implementation section '$class' in config.");
        }

        require_once $filepath;

        if (!class_exists($classname, $autoload = false))
        {
            throw new Immo_MobileCommerce_Exception("Class ".var_export($classname, true)." is not defined.");
        }
        
        return $classname;
    }

    protected function replaceObjectInParams(&$params)
    {
        if (is_array($params))
        {
            foreach ($params as $key => $paramVal)
            {
                if  ((!is_array($paramVal)) && (strpos($paramVal, "##") === 0))
                {
                    $newObjParamName = trim(str_replace("##", "", $paramVal));
                    $objectParam = $this->getIOCObject($newObjParamName);
                    $params[$key] = $objectParam;
                }
                if (is_array($paramVal))
                {
                    foreach ($paramVal as $key2 => $paramVal2)
                    {
                        if  ((!is_array($paramVal2)) && (strpos($paramVal2, "##") === 0))
                        {
                            $newObjParamName = trim(str_replace("##", "", $paramVal2));
                            $objectParam = $this->getIOCObject($newObjParamName);
                            $params[$key][$key2] = $objectParam;
                        }
                    }
                }
            }
        }
    }

    public function getIOCObject($objectName, $scheme = '<default>')
    {

        //Profiler::profileStart(__METHOD__);
        $res = $this->getIOCObjectRaw($objectName, $scheme);
        //Profiler::profileEnd(__METHOD__);
        return $res;
    }

    protected function getStandartObjects($objectName, $implementationName, $constructParams)
    {
        if (strtoupper($implementationName) == "ARRAY")
        {
            $resultObj = $constructParams;
            $this->_iocContainer[$objectName] = $resultObj;
            return $resultObj;
        }
        return null;

    }

    protected function getIOCObjectRaw($objectName, $scheme = '<default>')
    {


        $iocSection = $this->get($objectName, 'IOC');
        if (!$iocSection)
        {
            throw new Immo_MobileCommerce_Exception("No Ioc section '$objectName' in config.");
        }
        if (!is_array($iocSection))
            return $this->getIOCObjectRaw($iocSection, $scheme);
        //$isPersistent = @$iocSection[$scheme]['IsPersistent'];
		$isPersistent = isset($iocSection[$scheme]['IsPersistent']) ? $iocSection[$scheme]['IsPersistent'] : true;
        if ($isPersistent && (isset($this->_iocContainer[$objectName])) )
        {
            return $this->_iocContainer[$objectName];
        }

        $implementationName = @$iocSection[$scheme]['Implementation'];
        if (!$implementationName)
        {
            throw new IImmo_MobileCommerce_Exception("No Implementation for ioc section '$objectName' in config.");
        }

        $constructMethod = @$iocSection[$scheme]['ConstructMethod'];
        if (!$constructMethod)
        {
            throw new Immo_MobileCommerce_Exception("No ConstructMethod for ioc section '$objectName' in config.");
        }

        $constructParams = isset($iocSection[$scheme]['ConstructParams']) ? $iocSection[$scheme]['ConstructParams'] : null;

        if (is_null($constructParams))
        {
            throw new Immo_MobileCommerce_Exception("No ConstructParams for ioc section '$objectName' in config.");
        }
        $standartObject = $this->getStandartObjects($objectName, $implementationName, $constructParams);
        if ($standartObject != null)
            return $standartObject;

        $classObj = $this->getImplementation($implementationName);
        $this->replaceObjectInParams($constructParams);
        /*if (is_array($constructParams))
        {
            foreach ($constructParams as $key => $paramVal)
            {
                if  ((!is_array($paramVal)) && (strpos($paramVal, "##") === 0))
                {
                    $newObjParamName = trim(str_replace("##", "", $paramVal));
                    $objectParam = $this->getIOCObject($newObjParamName);
                    $constructParams[$key] = $objectParam;
                }
            }
        }*/

        if ($constructMethod == '#')
        {
            if ($constructParams == '')
            {
                $resultObj = new $classObj();
            }
            else
            {
                 if (!is_array($constructParams))
                    $constructParams = array($constructParams);
                 $ref = new ReflectionClass($classObj);
                 $resultObj = $ref->newInstanceArgs($constructParams);
            }
        }
        else
        {
            if(is_callable(array($classObj, $constructMethod)))
            {
                $resultObj = call_user_func_array(array($classObj, $constructMethod), $constructParams);
            }
            else
            {
                throw new Immo_MobileCommerce_Exception("No ConstructMethod '$constructMethod' in IOC '$objectName' in config.");
            }
        }
        //вызываем метод инициализации
        $initMethod = isset($iocSection[$scheme]['InitMethod']) ? $iocSection[$scheme]['InitMethod'] : null;
        if (!is_null($initMethod))
        {
            $initParams = isset($iocSection[$scheme]['InitParams']) ? $iocSection[$scheme]['InitParams'] : null;
            $this->replaceObjectInParams($initParams);
            if(is_callable(array($resultObj, $initMethod)))
            {
                call_user_func_array(array($resultObj, $initMethod), $initParams);
            }
            else
            {
                throw new Immo_MobileCommerce_Exception("No InitMethod '$initMethod' in IOC '$objectName' in config.");
            }
        }

        $this->_iocContainer[$objectName] = $resultObj;
        return $resultObj;



    }

    public function getConfigFileName()
    {
        return $this->filename;
    }
}
