<?php

abstract class ScheduledJobActionAbstract
{
	const TYPE_SET_OBJECT_PROPERTY = 2;

	public abstract function run($params);
	public abstract function check($params);
	public abstract function getInfo();
	
	protected $params = null;
	protected $info = null;

	protected static $storage = array();
	protected static $isInit = false;


	protected static $conf = null;

	public static function initConf()
	{
		$path = dirname(__FILE__) . "/../conf.php";
		if (!is_array(self::$conf))
			self::$conf = include($path);
	}

	public static function getInfoForWeb()
	{
		self::initConf();
		$res = array();
		foreach (self::$conf as $key => $value)
		{
			$res[] = array("ID" => $key, "TITLE" =>$value);
		}

		return $res;
	}

	public function setParam($val)
	{
		$this->params = $val;
	}

	public function getParam($name)
	{
		return $this->params;
	}

	public static function getTypeNameById($typeId)
	{
		self::initConf();
		return isset(self::$conf[$typeId]) ? self::$conf[$typeId] : "N/A";
	}

	public static function getActionByTypeId($typeId)
	{
		self::initConf();
		if (self::$isInit == false)
		{
			foreach (self::$conf as $typeId => $className)
			{
				require_once dirname(__FILE__) ."/". $className. ".php";
				self::$storage[$typeId] = new $className;
			}
			self::$isInit = true;
		}

		return self::$storage[$typeId];
	}

} 