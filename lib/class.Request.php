<?php

class Request
{
	//protected $urlParams;
	//protected $postParams
	protected static $instance;

	protected $request = array();
	protected $urlParams;
	protected $postParams;

	protected function __construct()
	{
		$this->request = $_REQUEST;
		$this->urlParams = $_GET;
		//$this->postParams = $_POST;
		//var_dump('constr');

		//var_dump('qwe');
	}

	public function getParam($param)
	{
		if (isset($this->request[$param]))
			return $this->request[$param];
		return null;
	}

	public function getParams()
	{
		return $this->request;
	}

	public function getUrlParam($param)
	{
		if (isset($this->urlParams[$param]))
			return $this->urlParams[$param];
		return null;
	}

	public function getUrlParams()
	{
		return $this->urlParams;
	}

	public static function getInstance()
	{
		if (!self::$instance)
			self::$instance = new self();
		return self::$instance;
	}

}