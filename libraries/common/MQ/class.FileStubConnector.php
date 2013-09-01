<?php
/**
 * Immo_MQ_FileStubConnector
 * Класс для записи и чтения из фала XML сообщений
 */

require_once 'pbr-lib-common/src/MQ/abstract.Connector.php';

class Immo_MQ_FileStubConnector extends Immo_MQ_Connector
{
	public function beginT(){}
	public function commitT($transactioId){}
	public function abortT($transactioId){}
	public function getWithoutValidate(){}
	public function subscribe(){}
	public function unsubscribe(){}

	public function send(Immo_MQ_Message $message){
		//формируем путь для записи файла
		$path = $this->_channelName;
		//формируем имя файла
		$name = $message->getCorrelationID();
		//формируем содержимое файла
		$body = $message->getAsXml();
		try
		{
			//создаем файл с расширением .msg
			$fp = fopen($path.$name.'.msg', 'w');
			//пишем в него нашу тушку
			fwrite($fp, $body);
			//закрываем файл
			fclose($fp);
		}
		catch(Exception $exc)
		{
			echo $exc->getMessage();
		}

	}

	public function get(){
		//формируем путь для получения файла
		$path = $this->_channelName;
		//выбираем все файлы с расширением msg в нашей папочке
		$files = glob($path.'*.msg');
		//выдаем первый попавшийся файл
		if(isset($files[0]))
			echo file_get_contents($files[0]);
		else
			throw new Exception('ERROR: directory '.$path.' is empty');
	}

	public function ack(Immo_MQ_Message $message){
		//формируем путь для удаления фала
		$path = $this->_channelName;
		//формируем имя файла который будем удалять
		$name = $message->getCorrelationID();
		//удаляем данный файл
		unlink($path.$name.'.msg');
	}

	public function nack(Immo_MQ_Message $message){
		//формируем путь для удаления фала
		$path = $this->_channelName;
		//формируем имя файла который будем удалять
		$name = $message->getCorrelationID();
		//удаляем данный файл
		unlink($path.$name.'.msg');
	}
}