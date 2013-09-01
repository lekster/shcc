<?php

require_once 'libraries/internal/php5/phpconf/common/class.Reader.php';
require_once 'libraries/internal/php5/mobile-commerce/common/src/Config/class.Config.php';
require_once 'libraries/internal/php5/database/class.Facade.php';
require_once 'libraries/internal/php5/mobile-commerce/common/src/Mapper/class.ConfigMapper.php';


class Immo_MobileCommerce_ConfigDb extends Immo_MobileCommerce_Config
{
    protected $_configMapper;

    public function __construct($filename, $rootElemName = null, $datasourceConfigName = 'Data')
    {
        parent::__construct($filename);
        $currentWorkDir = realpath('.');
    	$location = 'sandbox';
        if (strpos($currentWorkDir, '/stable/') !== false)
            $location = 'stable';
        if (strpos($currentWorkDir, '/devel/') !== false)
            $location = 'devel';
        $datasource = @$this->get('Datasource', $datasourceConfigName);
        try
		{
			$datasourceFile = "config/data/database.$datasource.ini";
			if (is_readable($datasourceFile))
			{
				$connection = new ImmoDatabaseFacade(new ImmoDataBaseConfig($datasourceFile));
				$this->_configMapper = new Immo_MobileCommerce_ConfigMapper($connection);
			}
		}
		catch (Exception $e)
		{
		
		}
		
        $host = trim(`hostname`);
    	$hostname = substr($host, 0, strpos($host, "."));

        $this->loadConfigs($rootElemName, $location, $hostname);
    }

    protected function loadConfigs($rootElemName, $location = null, $hostName = null)
    {
        $rootElem = null;
		if (is_object($this->_configMapper))
			$rootElem = $this->_configMapper->getRootElem($rootElemName);
        if (is_object($rootElem))
        {
            $childs = $this->_configMapper->getAllChilds($rootElem);
            foreach ($childs as $child)
            {
                if ($child->getKey() == $hostName)
                    $rootElem = $child;
            }

            $childs = $this->_configMapper->getAllChilds($rootElem);
            foreach ($childs as $child)
            {
                if ($child->getKey() == $location)
                    $rootElem = $child;
            }

            //var_dump($rootElem);
            $ret = $this->getChildVal($rootElem);
            //var_dump($ret[$rootElem->getKey()]);
            //var_dump($this->toArray());
            if (is_array(@$ret[$rootElem->getKey()]))
            {
                $keys = array_keys($ret[$rootElem->getKey()]);
                foreach($keys as $key)
                {
                    $dbArr = is_array($ret[$rootElem->getKey()][$key]) ? $ret[$rootElem->getKey()][$key] : array();
                    $this->data[$key] = array_merge($this->data[$key], $dbArr);
                }
            }
            
        }
    }

    protected function getChildVal($parentElem)
    {
        $childsArr = $this->_configMapper->getAllChilds($parentElem);
        //var_dump($childsArr);die();
        if (empty($childsArr))
        {
            //var_dump(array("'" . $parentElem->getKey() . "'" => $parentElem->getValue()));
            return array($parentElem->getKey()=> $parentElem->getValue());
        }
        $resultArr = array();
        foreach($childsArr as $child)
        {
            //var_dump($this->getChildVal($child));
            //$resultArr = array_merge_recursive($resultArr, $this->getChildVal($child));
            $resultArr = $resultArr + $this->getChildVal($child);
        }
        return array($parentElem->getKey() => $resultArr);
    }


    
    
}
