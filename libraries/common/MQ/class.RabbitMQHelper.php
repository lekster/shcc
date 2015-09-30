<?php

require_once 'pbr-lib-common/src/Caller/class.Caller.php';


class RabbitMQ_Helper
{
	private $baseUrl;
	private $login;
	private $pass;
	
	public function __construct($baseUrl, $login, $pass)
	{
		$this->baseUrl = $baseUrl;
		$this->login = $login;
		$this->pass = $pass;
	}
	
	
	public function getRawNodesInfo() 
	{
		return $this->readUrl($this->baseUrl . "/api/nodes", $this->login, $this->pass);
	}
	
	public function getOverviewInfo() 
	{
		return $this->readUrl($this->baseUrl . "/api/overview", $this->login, $this->pass);
	}
	
	public function getRabbitHostName()
	{
		$arr = parse_url($this->baseUrl);	
		return $arr['host'];
	}
	
	public function getRawQueuesInfo() 
	{
		return $this->readUrl($this->baseUrl . "/api/queues", $this->login, $this->pass);
	}
	
	private function readUrl($urlString, $username, $password) 
	{
		$caller = new Caller($urlString, "IGNORE");
		$caller->setCurlOpt(CURLOPT_USERPWD, $username . ":" . $password); 
		$result = $caller->call();
		return $result;
	}



}