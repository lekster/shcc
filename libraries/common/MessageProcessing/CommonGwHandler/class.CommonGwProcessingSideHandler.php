<?php

require_once "pbr-lib-common/src/MessageProcessing/CommonGwHandler/Entity/class.InitPaymentRequest.php";
require_once "pbr-lib-common/src/MessageProcessing/CommonGwHandler/Entity/class.InitPaymentResponse.php";
require_once "pbr-lib-common/src/MessageProcessing/CommonGwHandler/Entity/class.PaymentAuthorizationRequest.php";
require_once "pbr-lib-common/src/MessageProcessing/CommonGwHandler/Entity/class.PaymentCancellationRequest.php";
require_once "pbr-lib-common/src/MessageProcessing/CommonGwHandler/Entity/class.PaymentContractRequest.php";
require_once "pbr-lib-common/src/MessageProcessing/CommonGwHandler/Entity/class.PaymentContractResponse.php";
require_once "pbr-lib-common/src/MessageProcessing/CommonGwHandler/Entity/class.InitPaymentAdditionalParams.php";
require_once "pbr-lib-common/src/MessageProcessing/CommonGwHandler/Entity/class.PaymentAuthorizationResponse.php";
require_once "pbr-lib-common/src/MessageProcessing/CommonGwHandler/Entity/class.PaymentCancellationResponse.php";
require_once 'pbr-lib-common/src/Helper/UUIDGenerator.php';


abstract class CommonGwProcessingSideHandler
{

    protected $logger;
    protected $config;
    protected $returnAddress = null;
    protected $paymentIdPrefix = "";
    protected $paymentStatusCallbackAddress = null;
    protected $graphiteMetricLocation = null;
    protected $graphiteSender = null;

    public function __construct()
    {
        $this->logger = Immo_MobileCommerce_ServiceLocator::getInstance()->getLogger();
        $this->config = Immo_MobileCommerce_ServiceLocator::getInstance()->getConfig();
    }

    public function getPaymentIdPrefix()
    {
        return $this->paymentIdPrefix;
    }

    public function setPaymentIdPrefix($paymentIdPrefix)
    {
        if ($paymentIdPrefix != null && trim($paymentIdPrefix) != "")
            $this->paymentIdPrefix = $paymentIdPrefix . "_";
        else
            $this->paymentIdPrefix = "";
    }

    public function getReturnAddress()
    {
        return $this->returnAddress;
    }

    public function setReturnAddress($returnAddress)
    {
        $this->returnAddress = $returnAddress;
    }

    public function setGraphiteMetricLocation($val, $senderIocName = 'AnalyticsStatsdSender')
    {
        $this->graphiteMetricLocation = $val;
        if (!is_null($this->config->get($senderIocName, 'IOC'))) {
            $this->graphiteSender = $this->config->getIOCObject($senderIocName);
        }
    }

    public function getGraphiteMetricLocation()
    {
        return $this->graphiteMetricLocation;
    }

    public function getPaymentStatusCallbackAddress()
    {
        return $this->paymentStatusCallbackAddress;
    }

    public function setPaymentStatusCallbackAddress($paymentStatusCallbackAddress)
    {
        $this->paymentStatusCallbackAddress = $paymentStatusCallbackAddress;
    }

    public function initPayment($mqMessage)
    {
        //определяем что за запрос к нам пришел и отправляем на правильный обработчик
        $ak = array_keys($mqMessage->getMQMessageData());
        $fm = isset($ak[0]) ? $ak[0] : "";
        $this->logger->debug("format indicator|" . $mqMessage->getFormatIndicator() . "|" . $fm);

        if ($mqMessage->getFormatIndicator() == "InitPaymentRequest" || $fm == "InitPaymentRequest") {
            $request = InitPaymentRequest::parseFromArray($mqMessage->getMQMessageData()); //Формируем объект запроса
            if ($request != null) {
                $this->logger->debug("UUID|" . $request->getPaymentUUID());

                $startTime = microtime(true);

                $params = InitPaymentAdditionalParams::parseStr($request->getAdditionalParams());
                $resp = $this->checkRequestAndSetErrorResponse($request, $params); //Валидация параметров. ЭТОТ МЕТОД ОТНОСИТСЯ К КОНКРЕТНОЙ ЗАДАЧЕ - ПЕРЕПИСАТЬ!
                if ($resp == null) { //Валидация прошла
                    $resp = $this->initPaymentTemplate($request, $params); //Отправка запроса. ЭТОТ МЕТОД ОТНОСИТСЯ К КОНКРЕТНОЙ ЗАДАЧЕ - ПЕРЕПИСАТЬ!
                }
                $time = microtime(true) - $startTime;
                if (!is_null($this->graphiteSender) && !is_null($this->getGraphiteMetricLocation())) {
                    $this->graphiteSender->timing($this->getGraphiteMetricLocation() . '.initPayment', $time * 1000);
                }
                return $resp;

            } else {
				$this->logger->warn("InitPaymentRequest parse fail|" . $mqMessage->getCorrelationID());
				throw new Exception("InitPaymentRequest parse fail|" . $mqMessage->getCorrelationID());
			}
        } else {
            $this->logger->warn("Unknown message, drop it|" . $mqMessage->getCorrelationID());
            throw new Exception("Unknown message, drop it|" . $mqMessage->getCorrelationID());
        }


    }

    public function generateMqMessageFromInitPaymentResponse($correlationId, InitPaymentResponse $req)
    {
        $msgResp = new Immo_MQ_Message();
        $msgResp->setCorrelationId($correlationId);
        $msgResp->setMQMessageData($req->getAsArray());
        return $msgResp;
    }

    //return PaymentContractResponse
    public abstract function sendPaymentContract(PaymentContractRequest $req);

    //return PaymentCancellationResponse
    public abstract function sendPaymentCancellation(PaymentCancellationRequest $req);

    //return PaymentAuthorizationResponse
    public abstract function sendPaymentAuthorization(PaymentAuthorizationRequest $req);

    //return InitPaymentResponse
    protected abstract function initPaymentTemplate(InitPaymentRequest $req, InitPaymentAdditionalParams $params);

    /*вызывается перед initPaymentTemplate, служит для проверки входящих параметров, если проверка прошла - возвращать null*/
    //return InitPaymentResponse
    protected abstract function checkRequestAndSetErrorResponse(InitPaymentRequest $req, InitPaymentAdditionalParams $params);

}