<?php

require_once 'pbr-lib-common/src/MessageProcessing/CommonGwHandler/class.CommonGwProcessingSideHandler.php';

abstract class CommonGwProcessingSideHandlerPostXmlAsync extends CommonGwProcessingSideHandler
{
	/**
		@return PaymentContractResponse
	*/
	public function sendPaymentContract(PaymentContractRequest $req)
	{
		$msg = new Immo_MQ_Message();
		$msg->setCorrelationId(UUIDGenerator::v4());
		$msg->setMQMessageData($req->getAsArray());

		$postXmlCaller = $this->config->getIOCObject('PostXmlCallerCommonGwSender');
		$postXmlCaller->setMethod('PaymentContract');
		$sendResult = $postXmlCaller->send($msg);
		$postXmlCaller->setMethod(null);
		
		if ($sendResult)
		{
			$resp = $postXmlCaller->get();
			if (is_object($resp))
			{
				$obj = PaymentContractResponse::parseFromArray($resp->getMQMessageData());
				return $obj;
			}
		}
		else
		{
			return null;
		}
		return null;
	}
	
	public function sendPaymentCancellation(PaymentCancellationRequest $req)
	{		
		$this->logger->debug("sendPaymentCancellation", $req->getAsArray());
		//send to infocaller
		$msg = new Immo_MQ_Message();
		$msg->setCorrelationId(UUIDGenerator::v4());
		$msg->setMQMessageData($req->getAsArray());
		$res = $this->sendToInfocaller($msg, $req->getPaymentUUID(), "PaymentCancellation");
		
		if ($res !== true) return null;
		return new PaymentCancellationResponse();
	}

	public function sendPaymentAuthorization(PaymentAuthorizationRequest $req) {
		$this->logger->debug("sendPaymentAuthorization", $req->getAsArray());
		//send to infocaller
		$msg = new Immo_MQ_Message();
		$msg->setCorrelationId(UUIDGenerator::v4());
		$msg->setMQMessageData($req->getAsArray());
		$res = $this->sendToInfocaller($msg, $req->getPaymentUUID(), "PaymentAuthorization");
		
		if ($res !== true) return null;
		return new PaymentAuthorizationResponse();
	}
	
	
	public function sendToInfocaller($msg, $paymentUUID, $type)
	{
	    $callInfoParams = array
	    (
	    	'method' => 'POST',
			'successCode' => 202,
			'base64Data' => true,
			'data' => base64_encode($msg->getAsXml()),
	    );

	    $callInfo = array(
	    	"CallInfo" => array(
	    		"UUID" => $paymentUUID,
	    		"CallTime" => date('c'),
	    		"To" => $this->returnAddress . "?type=" . $type,
	    		"Params" => json_encode($callInfoParams),
	    		"TypeName" => "HTTPDATA",
	    		"AttemptCount" => 7,
	    		"FromService" => "CommonProcessing_" . get_class($this),
	    		"ToService" => "McCommonProcessingHandler",
	    		"Description" => "",
	    		"Payload" => "",
	    		"AdditionalParams" => "",
	    	),

	    );    
			
		$mqMsg = new Immo_MQ_Message();
		$mqMsg->setCorrelationID(UUIDGenerator::v4());
		$mqMsg->setMQMessageData($callInfo);
		$this->logger->debug($mqMsg->getAsXml());
		$infocallerSender = $this->config->getIOCObject('InfoCallerSender');
		$result = $infocallerSender->send($mqMsg);
		return $result;
	}
}