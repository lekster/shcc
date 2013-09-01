<?php

if(basename($_SERVER['SCRIPT_FILENAME'])==basename(__FILE__))
	exit;

/**
 * This demo webservice shows you how to work with PhpWsdl
 * 
 * @service SoapDemo
 */
class SoapDemo{
	/**
	 * Get a complex type object
	 * 
	 * @return ComplexTypeDemo The object
	 */
	public function GetComplexType(){
		return new ComplexTypeDemo();
	}
	
	/**
	 * Print an object
	 * 
	 * @param ComplexTypeDemo $obj The object
	 * @return string The result of print_r
	 */
	public function PrintComplexType($obj){
		return utf8_encode($this->PrintVariable($obj));
	}

    /**
     * Print an object
     *
     * @param string $id Some name (or an empty string)
     * @return array The result of print_r
     */
    public function getPlaylistByID($id)
    {
        $arr = MobileRadio_CRM_Playlist_PlaylistModel::load(array('playlist_id'=>$id));
        if($arr instanceof MobileRadio_CRM_Playlist_PlaylistModel)
        {
            $atrs = $arr->getAttributes();
            return $atrs;
        }
        return null;
    }

    /**
     * Print an object
     *
     * @param string $msisdn Some name (or an empty string)
     * @return array The result of print_r
     */
    public function getSubscriberByMSISDN($msisdn)
    {
        $arr = MobileRadio_CRM_Subscriber_SubscriberModel::load(array('msisdn'=>$msisdn));
        if($arr instanceof MobileRadio_CRM_Subscriber_SubscriberModel)
        {
            $atrs = $arr->getAttributes();
            return $atrs;
        }
        return null;
    }

    /**
     * Print an object
     *
     *
     * @return array
     */
    public function getServiceList()
    {
        $arr = MobileRadio_CRM_ServiceList_ServiceListModel::loadAllAttributes();
        $arr = array('543'=>array('name'=>'Родители','numbers'=>array('3221547','123123')));
        if(count($arr))
        {
            return $arr;
        }
        return null;
    }
	/**
	 * Print an array of objects
	 * 
	 * @param ComplexTypeDemoArray $arr A ComplexTypeDemo array
	 * @return stringArray The results of print_r
	 */
	public function ComplexTypeArrayDemo($arr){

        $arr = MobileRadio_CRM_Playlist_PlaylistModel::loadAllAttributes();
		$res=Array();
		$i=-1;
		$len=sizeof($arr);
		while(++$i<$len)
			$res[]=$this->PrintVariable($arr[$i]);
		return $res;
	}
	
	/**
	 * Say hello demo
	 * 
	 * @param string $name Some name (or an empty string)
	 * @return string Response string
	 */
	public function SayHello($name=null){
		$name=utf8_decode($name);// Because a string parameter may be UTF-8 encoded...
		if($name=='')
			$name='unknown';
		return utf8_encode('Hello '.$name.'!');// Because a string return value should by UTF-8 encoded...
	}

	/**
	 * This method has no parameters and no return value, but it is visible in WSDL, too
	 */
	public function DemoMethod(){
	}
	
	/**
	 * This method should not be visible in WSDL - but notice:
	 * If the PHP SoapServer doesn't know the WSDL, this method is still accessable for SOAP requests!
	 * 
	 * @ignore
	 * @param unknown_type $var
	 * @return string
	 */
	public function PrintVariable($var){
		return print_r($var,true);
	}
}
