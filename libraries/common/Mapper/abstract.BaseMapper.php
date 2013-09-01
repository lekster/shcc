<?php

require_once 'pbr-lib-common/src/ServiceLocator/class.ServiceLocator.php';

/**
 * Базовый маппер
 *
 */
class Immo_MobileCommerce_BaseMapper
{
    /**
     * @var ImmoDatabaseFacade Объект фасада к БД
     */
    protected $_connection;

	protected $_logger;
    protected $_config;

	protected $_scheme;

	/**
	 * Конструктор класса. Устанавливает соединение с базой данных
	 *
	 * @param ImmoDatabaseFacade $connection
	 * @return void
	 */
    public function __construct(Database_Facade $connection, $schema = null)
    {
		$this->_connection = $connection;
		$this->_logger = Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger();
		$this->_config = Immo_MobileCommerce_ServiceLocator::getInstance()->getConfig();
		$this->_scheme = $this->_config->get('Schema', 'Data');
		if ($schema != null) $this->_scheme = $schema;
		$this->_logger->debug('CONNECTION STRING', $connection->getConnection()->getDSN());
		$this->_logger->debug('DATABASE SCHEMA', $this->_scheme);
    }

	public function getPartner()
	{
		return Immo_MobileCommerce_ServiceLocator::getInstance()->getPartner();
	}

    public function getUser()
    {
        return Immo_MobileCommerce_ServiceLocator::getInstance()->getUser();
    }

    protected function _makeObject($res, $className)
    {
		if (!$row = $res->fetchRow()) { return null; }
        return new $className($row);
    }

    protected function _makeArrayOfObjects($res, $className)
    {
		if ($res->getSelectedRowsNumber() < 1) { return array(); }
        $obj = array();
        foreach ($res->fetchAllRows() as $row)
        {
            $obj[] = new $className($row);
        }
        return $obj;
    }

    protected function _makeAssociativeArrayOfObjects($res, $className, $key)
    {
        if ($res->getSelectedRowsNumber() < 1) { return array(); }

        $obj = array();
        foreach ($res->fetchAllRows() as $row)
        {
            //var_dump($key, $row[$key], $row); die();
            $obj[$row[$key]] = new $className($row);
        }
        return $obj;
    }

    public function beginTransaction()
    {
        $this->_connection->query("BEGIN");
    }

    public function commit()
    {
        $this->_connection->query("COMMIT");
    }

    public function rollback()
    {
        $this->_connection->query("ROLLBACK");
    }
}
