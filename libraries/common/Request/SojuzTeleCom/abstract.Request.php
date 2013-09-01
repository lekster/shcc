<?php

require_once 'libraries/internal/php5/mobile-commerce/common/src/Request/SojuzTeleCom/class.Check.php';
require_once 'libraries/internal/php5/mobile-commerce/common/src/Request/SojuzTeleCom/class.Confirm.php';
require_once 'libraries/internal/php5/mobile-commerce/common/src/Request/SojuzTeleCom/class.Status.php';

/**
 * Абстрактный класс Immo_MobileCommerce_SojuzTeleCom_Request
 *
 * @copyright Inform-Mobil
 * @author Vadim Kurochkin <kurochkin@immo.ru>
 */
 
/*
Если запрос является запросом check

Пользовательские сообщения вывести в полиглот или прописать статично в Системе

К нам пришли поля
id
phone
datetime
shortphone
msgbody
control
cmd

Возможно нужны не валидаторы а парсеры


// Принимаем POST (или GET), возвращаем XML
// Можно ли если сервис не отвечает перенатравлять запрос, который будет сообщать о временных проблемах?

// Впихнуть куда нибудь шаблоны полиглота с определенного профиля

// Принять запрос от Союзтелекома. Есть какой-то объект, который парсит и выдает action = check, confirm, status - частный для Союзтелеком
// check. Надо сделать запрос к партнеру - проверить существование ЛС
	// Провести валидацию msgbody. То, что [преф] [лицевой счет в каком-то формате] [сумма денег в каком-то формате] - частный для Урала
	// ok
		// Сформировать данные для http-запроса: URL, тело запроса - частный для Урала
		// next
			// Обработчик, делающий http-запрос - общий, можно сделать несколько имплементаций
			// ok
				// Разбираем ответ. Лицевой счет существует: ok|no|invalid_response - частный для Урала
				// ok
					// Отвечаем Союзтелекому, что ЛС существует - проверка заказа - OK - частный для Союзтелеком
				// no
					// Отвечаем Союзтелекому, что ЛС НЕ существует - проверка заказа - NOT OK - частный для Союзтелеком
				// invalid_response
					// Надо ответить Союзтелекому, что ошибка, попробуйте позже.
			// no Надо ответить Союзтелекому, что ошибка, попробуйте позже.
	// no
		// Отвечаем Союзтелекому, что формат запроса не соответствует требуемому - перманентная ошибка
// confirm. Подтверждение об оплате
	// Провести валидацию запроса - ЭТОГО НЕТ
	// ok
		// Получить транзакцию из БД
		// next
			// Разбираем запрос - оплатил или нет - частный для Урала
			// ok (оплатил)
				// Обновить статус транзакции, время оплаты, ...
			// no (НЕ оплатил)
				// Обновить статус транзакции, время оплаты, ...
		
	// no
		// Отвечаем Союзтелекому, что формат запроса не соответствует требуемому - перманентная ошибка

Крон увидит транзакции со статусом оплатил|неоплатил и отправит запрос партнеру.
Крон должен определить по конфигу, куда отправить запрос о статусе.

Нужно ответить Response

<?xml version="1.0" encoding="UTF-8"?>
<response>
<result>RESULT</result>
<info>ORDER_INFO</info>
<sum>SUM</sum>
<order>ORDER</order>
<answer>ANS</answer>
<descr>DESCRIPTION</descr>
</response>

*/

abstract class Immo_MobileCommerce_SojuzTeleCom_Request
{
    protected $_id;
    protected $_phone;
    // protected $_result;
    protected $_control;
    protected $_cmd;
    
    protected $_commands = array('check', 'confirm', 'status');
    
    public function __contruct($xmlStr)
    {
        $this->init($xmlStr);
    }
    
    public function init($xmlStr)
    {
        $xml = new SimpleXMLElement($xmlStr);
        $this->_id      = $xml->id;
        $this->_phone   = $xml->phone;
        // $this->_result  = $xml->result;
        $this->_control = $xml->control;
        $this->_cmd     = $xml->cmd;
    }
    
    public static function factory($xmlSrc)
    {
        $xml = new SimpleXMLElement($xmlStr);
        if (empty($xml->cmd) || !in_array($xml->cmd, $this->_commands))
        {
            throw new Immo_MobileCommerce_Exception('Invalid SojuzTeleCom request command: '.var_export($xml->cmd, true));
        }
        //return new {'Immo_MobileCommerce_SojuzTeleCom_Request_'.ucfirst($xml->cmd)}($xmlStr);
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function getPhone()
    {
        return $this->_phone;
    }
    
    // public function getResult()
    // {
        // return $this->_result;
    // }
    
    public function getControl()
    {
        return $this->_control;
    }
    
    public function getCmd()
    {
        return $this->_cmd;
    }
}
