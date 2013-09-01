<?php

require_once 'pbr-lib-common/src/Rest/class.ModelTask.php';

class ModelManager
{
	private static $_Instance = null;

	private $_TaskList;

	private $_MultiCurl;

	private $_Index;

	public function __construct()
	{
		$this->_MultiCurl = curl_multi_init();

		$this->_Index = 0;
	}

	public function __destruct()
	{
		curl_multi_close($this->_MultiCurl);
	}

	public static function Instance()
	{
		$className = __CLASS__;

		return self::$_Instance ?
			self::$_Instance : self::$_Instance = new $className();
	}

	public function PrepareTask(&$model, $methodName)
	{
		return $this->_TaskList[++$this->_Index] =
			&ModelTask::Prepare($model, $methodName);
	}

	public function HttpRequest($url, $method = "POST", $headers = array(), $postData = null)
	{
    	$curl = curl_init();

    	$curlOptions = array(
    		CURLOPT_URL => $url,
    		CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
    		CURLOPT_POST => isset($postData)
    	);

    	if (isset($postData))
    	{
    		$curlOptions[CURLOPT_POSTFIELDS] = $postData;
    	}

    	if (!empty($headers))
    	{
    		$curlOptions[CURLOPT_HTTPHEADER] = $headers;
    	}

    	curl_setopt_array($curl, $curlOptions);

    	$this->_TaskList[$this->_Index]->Request = $curl;

        curl_multi_add_handle($this->_MultiCurl, $curl);
	}

	public function Process($wait = false)
	{
        $running = true;

        while($running && $wait)
        {
			curl_multi_exec($this->_MultiCurl, $running);

			if (!$running)
			{
				foreach ($this->_TaskList as $task)
				{
					$task->ResponseInfo = curl_getinfo($task->Request);

					$response = curl_multi_getcontent($task->Request);

					$task->Headers = substr($response, 0, $task->ResponseInfo['header_size']);

					$task->Response = substr($response, $task->ResponseInfo['header_size']);

					$task->Model->setResult($task);

					$task->Model->setHeaders($task);

					curl_multi_remove_handle($this->_MultiCurl, $task->Request);

					curl_close($task->Request);
				}

				$this->_TaskList = array();
			}

			usleep(10000);
        }

		return $running;
	}
}