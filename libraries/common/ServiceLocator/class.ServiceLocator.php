<?php
require_once 'pbr-lib-common/src/Config/class.Config.php';
require_once 'pbr-lib-common/src/class.Exception.php';


class Immo_MobileCommerce_ServiceLocator
{
    protected static $_instance = null;
    protected $_data = array();

    public static function getInstance($configFilename = null)
    {
        if (empty(self::$_instance))
        {
            self::$_instance = new self($configFilename);
        }

        return self::$_instance;
    }

    protected function __construct($configFilename)
    {
        if (empty($configFilename))
        {
            throw new Immo_MobileCommerce_Exception("Empty config argument: ".var_export($configFilename, true));
        }

        $config = new Immo_MobileCommerce_Config($configFilename);
        $this->setConfig($config);
    }

    protected function _get($key)
    {
        if (!isset($this->_data[$key]))
        {
            /** ���� Lazy loading � ��� �����... */
            $this->{'_init'.$key}();
        }

        return $this->_data[$key];
    }

    protected function _set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     * Config
     */

    public function getConfig()
    {
        return $this->_get('Config');
    }

    public function setConfig(Immo_MobileCommerce_Config $config)
    {
        $this->_set('Config', $config);
    }


    /**
     * Partner
     */
    protected function _initPartner()
    {
    }

    public function getPartner()
    {
        return $this->_get('Partner');
    }

    public function setPartner(Immo_MobileCommerce_Partner $partner)
    {
        $this->_set('Partner', $partner);
    }

    /**
     * User
     */
    protected function _initUser()
    {
    }

    public function getUser()
    {
        return $this->_get('User');
    }

    public function setUser(Immo_MobileCommerce_User $user)
    {
        $this->_set('User', $user);
    }

    /**
     * Logger
     */

    protected function _initLogger()
    {
        try
        {
            $obj = $this->getConfig()->getIOCObject('Logger');
            $this->setLogger($obj);
        }
        catch(Exception $e)
        {
            $obj = null;
        }
        /*if ($obj == null)
        {
            $loggerClass = $this->getConfig()->getImplementation('Logger');
            $logger = call_user_func($loggerClass.'::getInstance');
            $this->setLogger($logger);
        }*/
    }

    public function getLogger()
    {
        return $this->_get('Logger');
    }

    public function setLogger(Immo_MobileCommerce_Loggable $logger)
    {
        $this->_set('Logger', $logger);
    }

    /**
     * Facade
     */

    protected function _initFacade()
    {
        $facadeClass = $this->getConfig()->getImplementation('Facade');
        $facade = call_user_func($facadeClass.'::getInstance');
        $this->setFacade($facade);
    }

    public function getFacade()
    {
        return $this->_get('Facade');
    }

    public function setFacade($facade)
    {
        $this->_set('Facade', $facade);
    }

    /**
     * CronLock
     */

    protected function _initCronLock()
    {
        try
        {
            $cronLock = $this->getConfig()->getIOCObject('CronLock');
            $this->setCronLock($cronLock);
        }
        catch(Exception $e)
        {
            $cronLock = null;
        }
        /*
        if ($obj == null)
        {
            $cronLockClass = $this->getConfig()->getImplementation('CronLock');
            $cronLock = new $cronLockClass($this->getConfig()->get('LockFilenameTemplate', 'CronLock'),$this->getLogger());
        }
         */
        $this->setCronLock($cronLock);
    }

    public function getCronLock()
    {
        return $this->_get('CronLock');
    }

    public function setCronLock(Immo_MobileCommerce_Lockable $cronLock)
    {
        $this->_set('CronLock', $cronLock);
    }

    /**
     * SoapClient
     */

    protected function _initSoapClient()
    {
        $soapClientClass = $this->getConfig()->getImplementation('SoapClient');
        $soapClient = call_user_func($soapClientClass.'::getInstance');
        $this->setSoapClient($soapClient);
    }

    public function getSoapClient()
    {
        return $this->_get('SoapClient');
    }

    public function setSoapClient($soapClient)
    {
        $this->_set('SoapClient', $soapClient);
    }

    /**
     * Event
     */

    protected function _initEvent()
    {
        $eventClass = $this->getConfig()->getImplementation('Event');
        $event = call_user_func($eventClass.'::getInstance');
        $this->setEvent($event);
    }

    public function getEvent()
    {
        return $this->_get('Event');
    }

    public function setEvent($event)
    {
        $this->_set('Event', $event);
    }
}
