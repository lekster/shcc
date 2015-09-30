<?php

class Curl {

    public static function downloadFile($url, $file)
    {
        $options=array(CURLOPT_FOLLOWLOCATION => true);
        $options[CURLOPT_URL] = $url;
		$options[CURLOPT_RETURNTRANSFER] = true;
        
        $ch = curl_init();
        $fp = fopen ($file, 'w+');
        curl_setopt_array($ch, $options);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILE, $fp); // write curl response to file
		$result = curl_exec($ch);
        curl_close($ch);
        fclose($fp);
		return $result;
    }

	public static function getWithRedirect($url) {
		return self::get($url, array(CURLOPT_FOLLOWLOCATION => true, CURLOPT_HEADER => false ));
	}

	public static function get($url, $options=array()) {
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_RETURNTRANSFER] = true;
		return self::_exec($options);
	}

	public static function putLocation($url) {
		return self::_findRedirectUrl($url, array(CURLOPT_PUT=>true));
	}

	public static function postLocation($url) {
		return self::_findRedirectUrl($url, array(CURLOPT_POST=>true));
	}

	private static function _findRedirectUrl($url, $options) {
		$options[CURLOPT_URL] = $url;
        //$options[CURLOPT_FOLLOWLOCATION]  = 0;
        $info = self::_exec($options, true);
        return $info['redirect_url'];
	}

	public static function putFile($url, $filename) {
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_PUT] = true;
		$handle = fopen($filename, "r");
		$options[CURLOPT_INFILE] = $handle;
		$options[CURLOPT_INFILESIZE] = filesize($filename);

		$info = self::_exec($options, true);

		return ('201' == $info['http_code']);
	}

	public static function postString($url, $string) {
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_POST] = true;
		$options[CURLOPT_POSTFIELDS] = $string;

		$info = self::_exec($options, true);

		return ('200' == $info['http_code']);
	}

	public static function put($url) {
		$options = array();
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_PUT] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;

		return self::_exec($options);
	}

	public static function post($url) {
		$options = array();
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_POST] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;

		return self::_exec($options);
	}

	public static function delete($url) {
		$options = array();
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_CUSTOMREQUEST] = "DELETE";
		$options[CURLOPT_RETURNTRANSFER] = true;

		return self::_exec($options);
	}

	private static function _exec($options, $returnInfo=false) {
		$ch = curl_init();
		//var_dump($options);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
        preg_match_all('/^Location:(.*)$/mi', $result, $matches);
        //echo $result . PHP_EOL;
		if ($returnInfo) {
			$result = curl_getinfo($ch);
            $result = array_merge($result, array('redirect_url' => !empty($matches[1]) ? trim($matches[1][0]) : ""));
		}
        /*var_dump("******************RESULT*******************");
        var_dump($result);
        var_dump("*************************************");
        */
		curl_close($ch);
		return $result;
	}
    

    
    
}
