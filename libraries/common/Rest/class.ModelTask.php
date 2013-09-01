<?php

class ModelTask
{
	public $Model;
	public $MethodName;
	public $Request;
	public $ResponseInfo;
	public $Response;
	public $Headers;

	public static function Prepare(&$model, $methodName)
	{
		$newTask = new self;

		$newTask->Model = &$model;
		$newTask->MethodName = $methodName;
		$newTask->Request = null;
		$newTask->ResponseInfo = null;
		$newTask->Response = null;
		$newTask->Headers = null;

		return $newTask;
	}
}