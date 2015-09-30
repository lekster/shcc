<?php

class Immo_MobileCommerce_Helper_DataStoreHelper
{
	const BASE_PATH = '/home/projects/data/pbr-lib-common/storage/';
	
	private $_filename;
	
	public function __construct($filename)
	{
		$this->_filename = self::BASE_PATH.$filename;
	}
	
	public function set($value, $params=null, $name=null) //сохраняет данные в файле
	{
		$curentName = $this->_filename;
		if(!empty($name))
			$curentName .= $name;
			
		$fp = fopen($curentName, "r+");
		$this->lock($fp);
		ftruncate($fp, 0);
		fwrite($fp, $value);
		$this->unlock($fp);
		fclose($fp);
		// Если переданы дополнительные параметры
		if($params)
		{
			// Если передана дата модификации
			if(!empty($params['modify']))
			{
				$modify = $params['modify'];
				if(!is_integer($modify)) 
					$modify = strtotime($modify);
				// Выставляем дату модификации файла на переданную
				touch($curentName, $modify);
			}
		}
	}

	public function get($name=null) //получает данные из файла
	{
		$curentName = $this->_filename;
		if(!empty($name))
			$curentName .= $name;
	
		$fp = fopen($curentName, "r");
		$this->lock($fp);
		$contents = fread($fp, filesize($curentName));
		$this->unlock($fp);
		fclose($fp);
		if(!empty($contents))
			return $contents;
		return false;
	}

	public function isExists($name=null) //проверяет наличие непустого файла
	{
		$curentName = $this->_filename;
		if(!empty($name))
			$curentName .= $name;
		
		return file_exists($curentName) && filesize($curentName);
	}

	public function create($name=null, $modify=null) //создает файл
	{
		$curentName = $this->_filename;
		if(!empty($name))
			$curentName .= $name;
		
		if(!empty($modify))
			touch($curentName, $modify);
		else
			touch($curentName);
		exec('chmod 777 '.$curentName);
	}

	public function lock($fp) //лочит файл
	{
		flock($fp, LOCK_EX);
	}

	public function unlock($fp) //анлочит
	{
		flock($fp, LOCK_UN);
	}

	public function delete($name=null) //удаляет файл
	{
		$curentName = $this->_filename;
		if(!empty($name))
			$curentName .= $name;
			
		if($this->isExists($name))
			unlink($curentName);
	}
	
	/*
	*	Далее идут функции работы с директориями (если в конструктор был передан каталог)
	*	При обнаружении фала вместо каталога, можно выставлять $this->_filename=dirname($this->_filename) - пока не пригодилось
	*/
	public function getCount() //получает количество файлов в директории
	{
		// Если filename не каталог возвращаем false
		if(!is_dir($this->_filename))
			return false;
		
		$files = array();
		exec('find '.$this->_filename.' -maxdepth 1 -type f', $files);
		
		return count($files);
	}
	
	public function getCountByModify() //получает количество файлов в директории с датой модификации меньше текущей
	{
		// Если filename не каталог возвращаем false
		if(!is_dir($this->_filename))
			return false;
		
		$files = array();
		exec('find '.$this->_filename.' -maxdepth 1 -mmin +0 -type f', $files);
		
		return count($files);
	}
	
	public function findFirst() //получает первый файл
	{
		// Если filename не каталог возвращаем false
		if(!is_dir($this->_filename))
			return false;
			
		$files = array();
		exec('find '.$this->_filename.' -maxdepth 1 -type f', $files);
		$first_file = array_shift($files);
		
		return basename($first_file);
	}


	public function findAll($limit) //получает первые $limit файлов 
	{
		// Если filename не каталог возвращаем false
		if(!is_dir($this->_filename))
			return false;
			
		$keys = array();
		$files = array();
		exec('find '.$this->_filename.' -maxdepth 1 -type f', $files);
		$n = 0;
		foreach ($files as $filename)
		{
			if (++$n > $limit) break;
			$keys[] = basename($filename);
		}

		return $keys;
	}

	public function findByModify($limit, $minutesWhithoutModify=0) //получает первые $limit файлов с датой модификации меньше текущей (если не указано время прошедшее с модификации)
	{
		// Если filename не каталог возвращаем false
		if(!is_dir($this->_filename))
			return false;
			
		$keys = array();
		$files = array();
		exec('find '.$this->_filename.' -maxdepth 1 -mmin +'.$minutesWhithoutModify.' -type f', $files);
		$n = 0;
		foreach ($files as $filename)
		{
			if (++$n > $limit) break;
			$keys[] = basename($filename);
		}

		return $keys;
	}
}