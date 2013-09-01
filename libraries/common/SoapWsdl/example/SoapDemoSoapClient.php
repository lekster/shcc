<?php
/**
 * @service SoapDemoSoapClient
 */
class SoapDemoSoapClient{
	/**
	 * The WSDL URI
	 *
	 * @var string
	 */
	public static $_WsdlUri='http://pbr-wserv-book-reader.maimulin.fenrir.immo/soapIndex.php?WSDL';
	/**
	 * The PHP SoapClient object
	 *
	 * @var object
	 */
	public static $_Server=null;

	/**
	 * Send a SOAP request to the server
	 *
	 * @param string $method The method name
	 * @param array $param The parameters
	 * @return mixed The server response
	 */
	public static function _Call($method,$param){
		if(is_null(self::$_Server))
			self::$_Server=new SoapClient(self::$_WsdlUri);
		return self::$_Server->__soapCall($method,$param);
	}

	/**
	 * Get a complex type object
	 *
	 * @return ComplexTypeDemo The object
	 */
	public function GetComplexType(){
		return self::_Call('GetComplexType',Array(
		));
	}

	/**
	 * Print an object
	 *
	 * @param ComplexTypeDemo $obj The object
	 * @return string The result of print_r
	 */
	public function PrintComplexType($obj){
		return self::_Call('PrintComplexType',Array(
			$obj
		));
	}

	/**
	 * Print an object
	 *
	 * @param string $id Some name (or an empty string)
	 * @return array The result of print_r
	 */
	public function getPlaylistByID($id){
		return self::_Call('getPlaylistByID',Array(
			$id
		));
	}

	/**
	 * Print an object
	 *
	 * @param string $msisdn Some name (or an empty string)
	 * @return array The result of print_r
	 */
	public function getSubscriberByMSISDN($msisdn){
		return self::_Call('getSubscriberByMSISDN',Array(
			$msisdn
		));
	}

	/**
	 * Print an object
	 *
	 * @return array
	 */
	public function getServiceList(){
		return self::_Call('getServiceList',Array(
		));
	}

	/**
	 * Print an array of objects
	 *
	 * @param ComplexTypeDemoArray $arr A ComplexTypeDemo array
	 * @return stringArray The results of print_r
	 */
	public function ComplexTypeArrayDemo($arr){
		return self::_Call('ComplexTypeArrayDemo',Array(
			$arr
		));
	}

	/**
	 * Say hello demo
	 *
	 * @param string $name Some name (or an empty string)
	 * @return string Response string
	 */
	public function SayHello($name){
		return self::_Call('SayHello',Array(
			$name
		));
	}

	/**
	 * This method has no parameters and no return value, but it is visible in WSDL, too
	 *
	 */
	public function DemoMethod(){
		return self::_Call('DemoMethod',Array(
		));
	}
}