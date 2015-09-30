<?php

class Caller
{
	//базовый url типа http://test/index.php?param=%param%
	private $_baseUrl;
	public $_postFields;
	private $_contentType;
	private $_errorMessage;
	private $_errorCode;
	private $_httpCode = 200;
	protected $_curlInfo;
	protected $_curlOptions = array(
		CURLOPT_TIMEOUT => 30,//3600,
		CURLOPT_CONNECTTIMEOUT => 30,//200,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_SSL_VERIFYPEER => false,
	);

	public function __construct($baseUrl, $httpCode = null)
	{
		$this->_baseUrl = $baseUrl;

		if ($httpCode) $this->_httpCode = $httpCode;
	}


	public function setPost ($postFields, $contentType = 'text/plain')
	{
		$this->_postFields = $postFields;
		$this->_contentType = $contentType;
	}

    public function setCurlOpt ($key, $value) {
        $this->_curlOptions[$key] = $value;
    }

	public function call($params = array(), $isPostRequest = false)
	{
		//заменяем параметры на значения
		if(!empty($params))
		{
			foreach ($params as $key => $value)
				$this->_baseUrl = str_replace('%'.$key.'%', $value, $this->_baseUrl);
		}

		//инициализируем новый сеанс curl с нашим url с параметрами
		$curl = curl_init($this->_baseUrl);
		//убираем прямой вывод в браузер, указывая результатом передачи строку
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		//если передан флаг на наличие поста - усказываем пост
		if($isPostRequest)
		{
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: '.$this->_contentType));
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_postFields);
		}

		curl_setopt_array($curl, $this->_curlOptions);
		//выполняем запрос
		$result = curl_exec($curl);
		//получаем инфу по передаче
		$this->_curlInfo = curl_getinfo($curl);
		$this->_errorMessage = curl_error($curl);
		$this->_errorCode = curl_errno($curl);
		//закрываем соединение
		curl_close($curl);
		//анализируем результаты
		if ($this->_errorCode === 0 && ($this->_curlInfo['http_code'] == $this->_httpCode || $this->_httpCode=='IGNORE'))
		{
			if($result === NULL)
				return TRUE;
			return $result;
		}
		return null;
	}
	
	public function getResultCode()
	{
		return $this->_curlInfo['http_code'];
	}

	public function getLastErrorCode()
	{
		return $this->_errorCode;
	}

	public function getLastErrorDesc()
	{
		return $this->_errorMessage;
	}

	public function setTimeOut($sec)
	{
		if (is_integer($sec))
		{
			$this->_curlOptions[CURLOPT_TIMEOUT] = $sec;
			$this->_curlOptions[CURLOPT_CONNECTTIMEOUT] = $sec;
		}
	}
}
