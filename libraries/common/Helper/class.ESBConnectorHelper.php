<?php

require_once "pbr-lib-common/src/Helper/UUIDGenerator.php";
require_once "pbr-lib-common/src/Helper/class.CacheHelper.php";

class ESBConnectorHelper
{
    private $_config;
    
    private $_cache;
    private $_postXmlConnector;
    private $_postXmlConnectorName;
    private $_queueConnectorIocName;

    private $_moduleName;

    public function __construct($moduleName, $amqpConnectorName, $postXmlConnectorName)
    {
        $service_locator = Immo_MobileCommerce_ServiceLocator::getInstance();
        $this->_config = $service_locator->getConfig();

        //$this->_postXmlConnector = $this->_config->getIOCObject($postXmlConnectorName);
        $this->_postXmlConnectorName = $postXmlConnectorName;
        $this->_moduleName = $moduleName;        
        $this->_queueConnectorIocName = $amqpConnectorName;
        $this->_cache = new Immo_MobileCommerce_CacheHelper();

        /* new $impPostXMLConnector(null, null, null, null,
            'libraries/internal/php5/mobile-commerce/common/src/MQ/xsd/mq_message.xsd',
            'libraries/internal/php5/mobile-commerce/common/src/MQ/xsd/RpcCallResult.xsd',
            'http://vps2093.mtu.immo:8084/api/');
        */
        
    }

    public function getServiceInfo($methodName, $type)
    {
        $connector = $this->_config->getIOCObject($this->_postXmlConnectorName);

        $requestData = array(
            'MethodName' => 'GetServiceInfo',
            'MethodArgs' => json_encode(array("method_name" => $methodName, "type" => $type))
        );

            $message = new Immo_MQ_Message();
            $message->setCorrelationId(UUIDGenerator::v4());
            $message->setMQMessageData(array("RpcCall" => $requestData));
            $result = $connector->send($message);

        if ($result == "true")
        {
            $answer = $connector->get();
            $data = $answer->getMQMessageData();
            $decodedData = html_entity_decode(base64_decode($data['RpcCallResult']['Result']));
            $json_arr = json_decode($decodedData, true);
            return $json_arr;
        }
        else 
        { 
            return NULL;
        }
    }

    public function getNamedConf($name, $isFromCache = true)
    {
            if ($isFromCache)
        {
            $ret = $this->_cache->get("NamedConf_" . $name);
            if ($ret != null) return $ret;
        }

        $globConfInfoArr = $this->getServiceInfo('GetNamedConfNew', 'PostXml');
        $connector = $this->_config->getIOCObject($this->_postXmlConnectorName);
        $connector->setChannelName($globConfInfoArr['endpointExcahnge']);

        $requestData = array(
            'MethodName' => 'GetNamedConf',
            'MethodArgs' => $name
        );

        $message = new Immo_MQ_Message();
        $message->setCorrelationId(UUIDGenerator::v4());
        $message->setMQMessageData(array("RpcCall" => $requestData));
        $result = $connector->send($message);
        if ($result == "true")
        {
            $answer = $connector->get();
            $data = $answer->getMQMessageData();
            $decodedData = html_entity_decode($data['RpcCallResult']['Result']);
            $ret = base64_decode($decodedData);
            $this->_cache->set("NamedConf_" . $name, $ret, 300);
            return $ret;
        } else { return NULL; }
    }

    public function sendHeartBeat()
    {
        $la = sys_getloadavg();
        return $this->_sendEvent(20, "HeartBeat", "ActiveWorkers:1;ActiveWorkers2:1;AllWorkers:1;CloneWorkers:0;LA:".$la[1].";");
    }

    public function sendErrorEvent($description)
    {
        return $this->_sendEvent(27, "ErrorEvent", $description);
    }

    private function _sendEvent($code, $description, $payload)
    {
        $connector = $this->_config->getIOCObject($this->_queueConnectorIocName);
        $ret = $this->_cache->get("___GlobalEventExchangeName");
        if(!$ret)
        {
            $globalEventInfoArr = $this->getServiceInfo("EventGlobal", "MQ");
            $ret = $globalEventInfoArr['endpointExcahnge'];
            $this->_cache->set("___GlobalEventExchangeName", $ret, 300);
        }

        $connector->setChannelName($ret);

        $requestData = array(
            "EventGuid" => UUIDGenerator::v4(),
            "EventCode" => $code,
            "EventDescription" => $description,
            "EventDate" => date("Y-m-d")."T".date("H:m:s"),
            "Payload" => $payload,
            "ModuleName" => $this->_moduleName,
            "Host" => trim(`hostname`),
            "BaseInputQueueName" => ""
        );

        $message = new Immo_MQ_Message();
        $message->setCorrelationId(UUIDGenerator::v4());
        $message->setMQMessageData(array("Event" => $requestData));

        $result = $connector->send($message);

        return $result;
    }
}