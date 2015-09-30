<?php

require_once 'pbr-lib-common/src/MQ/class.Message.php';
require_once 'pbr-lib-common/src/MQ/abstract.Connector.php';


abstract class Immo_MessageProcessing_Abstract
{
	public function __construct()
	{

	}

	public function process($message)
	{
		return true;
	}

	public function clear()	{}
}