<?php


$data = array(
	
	array('data'=> '2013-12-01 00:00:00', 'val' =>  1),
	array('data'=> '2013-12-01 01:00:00' , 'val' =>   2),
	array('data'=> '2013-12-01 02:00:00' , 'val' =>   3),
	array('data'=> '2013-12-01 03:00:00' , 'val' =>   1),
	array('data'=> '2013-12-01 04:00:00' , 'val' =>   4),
	array('data'=> '2013-12-01 06:00:00', 'val' =>   4),
	array('data'=> '2013-12-01 07:00:00' , 'val' =>   1),
	array('data'=> '2013-12-01 08:00:00' , 'val' =>   1),
	array('data'=> '2013-12-01 09:00:00' , 'val' =>   5),
	array('data'=> '2013-12-01 10:00:00' , 'val' =>   1),
	array('data'=> '2013-12-01 11:00:00' , 'val' =>   2),
	array('data'=> '2013-12-01 12:00:00', 'val' =>   1),

);


$res = array();
for ($i = 0; $i < count($data); $i++)
{
	$v = $data[$i]['val'];
	//if (isset($data[$i+1]) && $data[$i+1]['val'] !== $data[$i]['val'])
	if (isset($data[$i+1]))
	{
		if (!isset($res[$v])) $res[$v] = 0;
		$res[$v] += strtotime($data[$i+1]['data']) - strtotime($data[$i]['data']);
	}
}

var_dump($res);
foreach ($res as $k =>$v)
{
	echo $k . "|" . (float)$v / 3600 . PHP_EOL;
}

array(5) {
  [1] =>
  int(18000)
  [2] =>
  int(7200)
  [3] =>
  int(3600)
  [4] =>
  int(10800)
  [5] =>
  int(3600)
}
1|5
2|2
3|1
4|3
5|1