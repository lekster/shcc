<?php

class Immo_Helper_ConvertTransactionToHDFS
{
	// Задаем последовательность, в которой параметры транзакции хранятся на HDFS
	static private $fields = array(
		'transaction_id',
		'transaction_guid',
		'parent_guid',
		'date_create',
		'date_create_unixtimestamp',
		'date_modify',
		'date_modify_unixtimestamp',
		'virgin_price',
		'sum',
		'sum_user',
		'sum_service_precise',
		'sum_pbsol',
		'sum_partner',
		'sum_client',
		'sum_service',
		'billing_status',
		'status',
		'status_history',
		'last_status_changed',
		'last_status_changed_unixtimestamp',
		'result_code',
		'result_description',
		'cancel_code',
		'cancel_description',
		'target',
		'version',
		'callback_code',
		'callback_description',
		'params',
		'additional_params',//задел на будущее, пока оставляем пустым
		'currency',//задел на будущее, пока оставляем пустым
		'comment',
		'legend',
		'msisdn',
		'operator_id',
		'operator_name',
		'operator_group_id',
		'operator_group_name',
		'init_ip_address',//задел на будущее, пока оставляем пустым
		'gps',//задел на будущее, пока оставляем пустым
		'geodata',//задел на будущее, пока оставляем пустым
		'service_id',
		'service_name',
		'service_sms_prefix',
		'processing_id',
		'processing_name',
		'route_id',
		'product_id',
		'product_name',
		'shopfront_product_id',//задел на будущее, пока оставляем пустым
		'shopfront_product_name',//задел на будущее, пока оставляем пустым
		'shopfront_product_params',//задел на будущее, пока оставляем пустым
		'partner_id',
		'partner_name',
		'callback_status',
		'accepting_payment_partner_id',
		'payment_id'
	);
	// Задаем кеш-контейнер для опереаторов
	static private $operators;
	
	static public function convertTransactionToHDFS($transaction){
		$first = true;
		// Заполняем инфу по оператору
		$opInfo = self::getOperatorInfoByOperatorID($transaction['operator_id']);
		$transaction['operator_name'] = $opInfo['name'];
		$transaction['operator_group_id'] = $opInfo['operator_group_info']['operator_group_id'];
		$transaction['operator_group_name'] = $opInfo['operator_group_info']['name'];
		// Формируем юникстаймы
		$transaction['date_create_unixtimestamp'] = strtotime($transaction['date_create']);
		$transaction['date_modify_unixtimestamp'] = strtotime($transaction['date_modify']);
		$transaction['last_status_changed_unixtimestamp'] = strtotime($transaction['last_status_changed']);
		// Кастомизируем некоторые поля
		$transaction['status_history'] = implode(",",$transaction['status_history']);
		$transaction['result_description'] = str_replace("\n","",$transaction['result_description']);
		$transaction['result_description'] = str_replace("\t","",$transaction['result_description']);
		$transaction['legend'] = str_replace("\n","#",$transaction['legend']);
		foreach(self::$fields as $name){
			// Делаем пустыми, значения которых пока нет
			if(!isset($transaction[$name]))
				$transaction[$name] = '';
			// Заполняем результирующую строку
			if($first){
				$result = $transaction[$name];
				$first = false;
			}
			else{
				$result .= '|'.$transaction[$name];
			}
		}
		return $result;
	}
	
	static public function getOperatorInfoByOperatorID($opId)
	{
		// Пытаемся достать из кеш-контейнера
		if(isset(self::$operators[$opId]))
			return self::$operators[$opId];
		// Если не достали, делаем запрос
		$context = stream_context_create(array('http' => array(
			'method' => 'POST',
			'header' => 'Content-Type: text/xml',
			'content' => xmlrpc_encode_request('GetOperatorInfoByOperatorID', array(array('op' => $opId))),
		)));
		$response = xmlrpc_decode(file_get_contents('http://vps7240.stable.mtu.immo/number-pool-api/web/index.php', false, $context));
		// Сохраняем в контейнер и возвращаем результат
		self::$operators[$opId] = $response;
		return $response;
	}
}