<?php

require_once 'pbr-lib-common/src/Rest/class.ModelManager.php';

abstract class ServiceModel
{
	protected $_ModelManager;

	protected $_Result;

	protected $_Headers;

	protected static $_ServiceName = null;

	private function extendObject($jsonObject)
	{
		if(is_object($jsonObject))
		{
			$className = get_called_class();

			foreach ($className::$_Fields as $fieldName => $properyList)
			{
				if (!isset($jsonObject->$fieldName))
				{
					$jsonObject->$fieldName = null;
				}
			}
		}
	}

	public function parseJSON($jsonResult)
	{
		$jsonObject = json_decode($jsonResult);

		if (!empty(static::$_Fields))
		{
			if (is_array($jsonObject))
			{
				foreach ($jsonObject as $jsonObjectItem)
				{
					$this->extendObject($jsonObjectItem);
				}
			}
			else
			{
				$this->extendObject($jsonObject);
			}
		}

		return $jsonObject;
	}

	public function setResult($task)
	{
		if (method_exists($task->Model, $task->MethodName."_Callback"))
		{
			$this->_Result = call_user_func_array(
				array($task->Model, $task->MethodName."_Callback"),
				array($task->Response, $task->ResponseInfo));
		}
		else
		{
			if (substr($task->MethodName, 1, 4) == "json")
			{
				$this->_Result = $this->parseJSON($task->Response);
			}
			else
			{
				$this->_Result = $task->Response;
			}
		}
	}

	public function setHeaders($task)
	{
		$this->_Headers = new stdClass();

		preg_match_all("/X-FM-([^\:]+):\s*([^\s]*)\s*\n/", $task->Headers, $matches, PREG_SET_ORDER);

		foreach ($matches as $match)
		{
			$this->_Headers->$match[1] = $match[2];
		}
	}

	public function HttpRequest($url, $method = "POST", $headers = array(), $postData = null)
	{
		return $this->_ModelManager->HttpRequest($url, $method, $headers, $postData);
	}

	public static function model(&$result = null, &$headers = null, $modelManager = null)
	{
		if (!isset($modelManager))
		{
			$modelManager = ModelManager::Instance();
		}

		$className = get_called_class();

		$instance = new $className();

		$instance->_ModelManager = $modelManager;

		$instance->_Result = &$result;

		$instance->_Headers = &$headers;

		return $instance;
	}

	protected static function getBaseByName($serviceName)
	{
		return $serviceName;
	}

	private static function getBase()
	{
		$serviceName = static::$_ServiceName ? static::$_ServiceName : get_called_class();

		return static::getBaseByName($serviceName);
	}

	public static function getAbsoluteUrl($url = '', $queryParameters = array())
	{
    	$absoluteUrl = self::getBase().$url;

    	if (!empty($queryParameters))
    	{
    		$iterator = 0;

    		foreach ($queryParameters as $parameterName => $parameterValue)
    		{
    			if ($parameterValue)
    			{
	    			switch(gettype($parameterValue))
	    			{
	    				case "array":
	    					$encodedValue = urlencode(join(",", $parameterValue));
	    					break;
	    				default:
	    					$encodedValue = urlencode($parameterValue);
	    			}

	    			$queryString = ($iterator++ ? "&" : "").
	    				urlencode($parameterName)."=".$encodedValue;
    			}
    		}

    		if (isset($queryString))
    		{
    			$absoluteUrl .= "?".$queryString;
    		}
    	}

		return $absoluteUrl;
	}

	public function __call($methodName, $parameters)
	{
		$task = null;

		if (method_exists($this, $methodName))
		{
			$this->_Result = $result = call_user_func_array(array(&$this, $methodName), $parameters);
		}
		else
		{
			$methodName = "_".$methodName;

			$task = $this->_ModelManager->PrepareTask($this, $methodName);

			if (method_exists($this, $methodName))
			{
				call_user_func_array(array(&$this, $methodName), $parameters);
			}
			else
			{
				throw new Exception("Method ".$methodName." not implemented in ".get_called_class());
			}
		}

		return $task;
	}
}