<?php


class ScheduledJobActionSetObjectProp extends ScheduledJobActionAbstract
{
	
	public function run($params)
	{
		preg_match_all("/([a-z0-9_-]+\.[a-z0-9_-]+)=([a-z0-9_-]+)/i", $params, $matches);
		if (isset($matches[1][0]) && (isset($matches[2][0])))
		{
			$obj = 	$matches[1][0];
			$val = $matches[2][0];
			sg($obj, $val);
			return true;
		}
		return false;
	}

	public function check($params)
	{
		$ret = true;
		$ret &= preg_match("/[a-z0-9_]+\.[a-z0-9_]+=[a-z0-9_]+/i", $params);
		
		preg_match_all("/([a-z0-9_-]+\.[a-z0-9_-]+)=([a-z0-9_-]+)/i", $params, $matches);
		if (isset($matches[1][0]))
		{
			$ret &= (getGlobal($matches[1][0]) == 0) ? false : true;
		}
		else
		{
			$ret = false;
		}
		
		return $ret;
	}

	public function getInfo()
	{
		return "Устанавливает значение для объекта\nиспользовать так: [Имя класса].[имя свойства]=[значение]" . PHP_EOL
		. "Например: ThisComputer.1w_temp=2\nОбъект должен существовать!!!";
	}

}