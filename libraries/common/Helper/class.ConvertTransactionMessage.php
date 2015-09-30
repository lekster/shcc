<?php

require_once 'pbr-lib-common/src/Entity/class.Transaction.php';
require_once 'pbr-lib-common/src/MQ/class.Message.php';


class Immo_Helper_ConvertTransactionMessage
{
	static public function convertMessageToTransaction(Immo_MQ_Message $message)
	{
		$msg_data = $message->getMQMessageData();

		if (empty($msg_data['TransactionObj'])) throw new Exception('Message is not TransactionObj');

		$t = $msg_data['TransactionObj'];

		$data = array(
			'transaction_guid' => $t['TransactionGuid'],
			'msisdn' => $t['Msisdn'],
			'status' => $t['Status'],
			'route_id' => $t['RouteId'],
			'parent_guid' => empty($t['ParentGuid'])? null : $t['ParentGuid'],
			'virgin_price' => $t['VirginPrice'],
			'target' => Immo_MobileCommerce_Transaction::TARGET_PBSOL_PARTNER_RPC,
			'service_id' => $t['ServiceId'],
			'product_id' => $t['ProductId'],
			'roadmap' => $t['ServiceId'] ? array($t['ServiceId']) : null,
			'operator_id' => $t['OperatorId'],
			'params' => $t['Params'],
			'sum' => $t['SumProcessing'],
			'sum_user' => $t['SumUser'],
			'sum_pbsol' => $t['SumPbsol'],
			'sum_partner' => $t['SumPartner'],
			'sum_service' => $t['SumService'],
			'sum_service_precise' => $t['SumServicePrecise'],
			'date_create' => $t['DateCreate'],
			'partner_id' => $t['PartnerId'],
			'user_id' => $t['UserId'],
			'accepting_payment_partner_id' => isset($t['ProcessingTransactionForeignId']) ? $t['ProcessingTransactionForeignId'] : null,
			'result_code' => isset($t['ResultCode']) ? $t['ResultCode'] : null,
			'result_description' => isset($t['ResultDescription']) ? $t['ResultDescription'] : null,
			'cancel_code' => isset($t['CancelCode']) ? $t['CancelCode'] : null,
			'cancel_description' => isset($t['CancelDescription']) ? $t['CancelDescription'] : null,
/*
			'' => $t['ParentGuid'],
			'' => $t['ProcessingId'],
			'' => $t['RuleId'],
			'' => $t['OperatorGroupId'],
			'' => $t['SumRaw'],
*/
		);

		return new Immo_MobileCommerce_Transaction($data);
	}

	static public function convertTransactionToMessage(Immo_MobileCommerce_Transaction $transaction)
	{
		$message = new Immo_MQ_Message();

		$data = array(
			'TransactionGuid' => $transaction->getTransactionGuid(),
			'ParentGuid' => $transaction->getParentGuid() ? $transaction->getParentGuid() : '',
			'Msisdn' => $transaction->getMsisdn(),
			'DateCreate' => !$transaction->getDateCreate() ? date('c') : date('c', strtotime($transaction->getDateCreate())),
			'Params' => htmlspecialchars(strval($transaction->getParams()), null, 'UTF-8'),
			'PartnerId' => $transaction->getPartnerId(),
			'UserId' => intval($transaction->getUserId()),
			'ProcessingId' => 0,
			'RuleId' => 0,
			'ServiceId' => $transaction->getServiceId(),
			'ProductId' => intval($transaction->getProductId()),
			'OperatorId' => intval($transaction->getOperatorId()),
			'OperatorGroupId' => 0,
			'ProcessingTransactionForeignId' => $transaction->getAcceptingPaymentPartnerId(),
			'SumRaw' => 0,
			'SumProcessing' => $transaction->getSum(),
			'SumUser' => $transaction->getSumUser(),
			'SumPbsol' => $transaction->getSumPbsol(),
			'SumPartner' => $transaction->getSumPartner(),
			'SumService' => $transaction->getSumService(),
			'SumServicePrecise' => $transaction->getSumServicePrecise(),
			'Status' => $transaction->getStatus(),
			'RouteId' => intval($transaction->getRouteId()),
			'VirginPrice' => intval($transaction->getVirginPrice()),
			'ResultCode' => $transaction->getResultCode(),
			'ResultDescription' => $transaction->getResultDescription(),
//			'SuccessMsg' => '',
//			'CancelMsg' => '',
//			'ValidateMsg' => '',
		);

		if (!$data['DateCreate']) $data['DateCreate'] = date('c');

		if (!$data['ProcessingTransactionForeignId']) unset($data['ProcessingTransactionForeignId']);

		if (!$data['ResultCode']) unset($data['ResultCode']);

		if (!$data['ResultDescription']) unset($data['ResultDescription']);

		$message->
			setCorrelationID(uuid_create())->
			setMQMessageData(array('TransactionObj' => $data));

		return $message;
	}
}
