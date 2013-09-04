<?php

class Say {
	function hello($to='world') {
		return "Hello $to!";
	}
	function hi($to) {
		return  "Hi $to!";
	}

    /**
    * @url POST method2
    */

    function add($n1, $n2)
    {
        return $n1 + $n2;
    }

        /**
     * @param int $n1
     * @param int $n2
     *
     * @return array
     */
    function multiply($n1, $n2)
    {
        return array(
            'result' => ($n1 * $n2)
        );
    }

    /**
     * @param string $objectName  {@from query}
     * @param string $propertyName {@from query}
     * @param string $value {@from query}
     * @return array
     */
    function getObjectProperty($objectName, $propertyName, $value)
    {
        //ThisComputer
        //1w_temp    

        $facade = Majordomo_Facade::getInstance();
        $prop = $facade->getPropertyToObjectByName($objectName, $propertyName);
        //$db = $facade->getDbConnection();
        //$set =  $db->getRepository('DbEntity\\SettingsEntity')->findAll();
        //var_dump($set);die();
        return (array('result'=> $prop));
        return array_sum(func_get_args());
    }

}