<?php

//передаем в GET параметрах 
//имя объекта, имя свойства, вреям выборки(date_start, date_end), 
//тип графика (1 - обычный, время/значение, 2 - группровка по значением, пирог с процентами
//3 - группировка по значениям, процент времени активности см скрипт 1.php


//агрегация данных - сек, мин, часы, дни, месяцы
//для 3 графика данные в агрегированном виде не подаются!!!

chdir(dirname(dirname(dirname(__FILE__))));
require_once "Hc/Highchart.php";


//http://192.168.1.120:85/Hc/graph/getGraph.php?obj=ThisComputer&property=d_on&graphType=1&aggregationType=0
//http://192.168.1.120:85/Hc/graph/getGraph.php?obj=ThisComputer&property=d_on&graphType=1&aggregationType=0&dateStart=2013-12-30
//http://192.168.1.120:85/Hc/graph/getGraph.php?obj=ThisComputer&property=1w_temp&graphType=1&aggregationType=2

$objectName = @$_GET['obj'];
$propertyName = @$_GET['property'];
$graphType = @$_GET['graphType'];
$aggregationType = @$_GET['aggregationType']; // 0 -sec, 1- min, 2 - hour, 3 - day, 4 - month, 5 - year
$dateStart = isset($_GET['dateStart']) ? $_GET['dateStart'] : '2000-01-01 00:00:00';
$dateEnd = isset($_GET['dateEnd']) ? date("Y-m-d H:i:s", strtotime($_GET['dateEnd'])) : date("Y-m-d H:i:s");

if (!isset($objectName) || !isset($propertyName) || !isset($graphType) || !isset($aggregationType))
{
	echo "usage: " . "http://192.168.1.120:85/Hc/graph/getGraph.php?obj=ThisComputer&property=d_on&graphType=1&aggregationType=0&dateStart=2013-12-30&dateEnd=2013-12-31";
	die();
}

//$objectName = 'ThisComputer';
//$propertyName = 'd_on';
//$graphType = 1;
//$aggregationType = 0; // 0 -sec, 1- min, 2 - hour, 3 - day, 4 - month, 5 - year
//$dateStart = '2013-12-01 00:00:00';
//$dateEnd = '2013-12-01 00:01:00';



//var_dump($propertyName); 
//var_dump(`pwd`);die();

//---------------------------- Standard inclusions 
 
 include_once("./lib/loader.php");
 require_once ("class.Facade.php");
 $facade = Majordomo_Facade::getInstance("./config/stable/global.php");

 include_once(DIR_MODULES."application.class.php");
 $db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); //connecting to database
 //include("./pChart/pData.class");   
 //include("./pChart/pChart.class");  
 
//die('asd');

$object = getObject($objectName);
//var_dump($object);

$prop_id = $object->getPropertyByName($propertyName, $object->class_id, $object->id);
//var_dump($prop_id);

$pvalue=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id."' AND OBJECT_ID='".$object->id."'");
//var_dump($pvalue);


if ($graphType == 1)
{

$sql = '';
switch ($aggregationType) 
{
	case 0:
		$sql = "SELECT * FROM phistory WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd' order by ADDED";
		# code...
		break;
	
	case 1:
		$sql = "
			select dt as ADDED, avg(VALUE) as VALUE from
			(
				SELECT DATE_FORMAT(ADDED, '%Y-%m-%d %H:%i') as dt, ID, VALUE, ADDED, VALUE_ID FROM phistory
				WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd' 
			) as foo
			group by dt order by dt
		";
		# code...
		break;

	case 2:
		$sql = "
			select dt as ADDED, avg(VALUE) as VALUE from
			(
				SELECT DATE_FORMAT(ADDED, '%Y-%m-%d %H') as dt, ID, VALUE, ADDED, VALUE_ID FROM phistory
				WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd' 
			) as foo
			group by dt order by dt
		";
		# code...
		break;

	case 3:
		$sql = "
			select dt as ADDED, avg(VALUE) as VALUE from
			(
				SELECT DATE_FORMAT(ADDED, '%Y-%m-%d') as dt, ID, VALUE, ADDED, VALUE_ID FROM phistory
				WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd' 
			) as foo
			group by dt order by dt
		";

		# code...
		break;	


	case 4:
		$sql = "
			select dt as ADDED, avg(VALUE) as VALUE from
			(
				SELECT DATE_FORMAT(ADDED, '%Y-%m') as dt, ID, VALUE, ADDED, VALUE_ID FROM phistory 
				WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd'
			) as foo
			group by dt order by dt
		";
		break;


	case 5:
		$sql = "
			select dt as ADDED, avg(VALUE) as VALUE from
			(
				SELECT DATE_FORMAT(ADDED, '%Y') as dt, ID, VALUE, ADDED, VALUE_ID FROM phistory 
				WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd'
			) as foo
			group by dt order by dt
		";
		break;


	default:
		# code...
		break;
}


$ph=SQLSelect($sql);
//var_dump($ph);

//$ph=SQLSelect("SELECT * FROM phistory WHERE VALUE_ID = {$pvalue['ID']} order by ADDED");
//var_dump($ph);




$yArray = array();
$xArray = array();

foreach ($ph as $value)
{
	$xArray[] = $value['ADDED'];
	$yArray[] = (float)$value['VALUE'];
}
//var_dump($yArray);

/********************************************************************/

$chart = new Highchart();

$chart->chart = array(
    'renderTo' => 'container',
    'type' => 'line',
    'marginRight' => 130,
    'marginBottom' => 25
);

$chart->title = array(
    'text' => $objectName . '|' . $propertyName,
    'x' => - 20
);
$chart->subtitle = array(
    'text' => 'Source: DB',
    'x' => - 20
);

$chart->xAxis->categories = $xArray;

$chart->yAxis = array(
    'title' => array(
        'text' => 'NNNN'
    ),
    'plotLines' => array(
        array(
            'value' => 0,
            'width' => 1,
            'color' => '#808080'
        )
    )
);
$chart->legend = array(
    'layout' => 'vertical',
    'align' => 'right',
    'verticalAlign' => 'top',
    'x' => - 10,
    'y' => 100,
    'borderWidth' => 0
);

$chart->series[] = array(
    'name' => $propertyName,
	'data' => $yArray,
);

$chart->tooltip->formatter = new HighchartJsExpr(
    "function() { return '<b>'+ this.series.name +'</b><br/>'+ this.x +': '+ this.y +'°C';}");

}

/***********************/

if ($graphType == 2)
{

$sql = '';
switch ($aggregationType) 
{
	case 0:
		$sql = "SELECT ADDED, VALUE, count(*) as cnt FROM phistory WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd' group by ADDED, VALUE order by ADDED, VALUE";
		# code...
		break;
	
	case 1:
		$sql = "
			select dt as ADDED, VALUE, count(*) as cnt from
			(
				SELECT DATE_FORMAT(ADDED, '%Y-%m-%d %H:%i') as dt, ID, VALUE, ADDED, VALUE_ID FROM phistory 
				WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd'
			) as foo
			group by dt, value order by dt, VALUE
		";
		# code...
		break;

	case 2:
		$sql = "
			select dt as ADDED, VALUE, count(*) as cnt from
			(
				SELECT DATE_FORMAT(ADDED, '%Y-%m-%d %H') as dt, ID, VALUE, ADDED, VALUE_ID FROM phistory 
				WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd'
			) as foo
			group by dt, value order by dt, VALUE
		";
		# code...
		break;

	case 3:
		$sql = "
			select dt as ADDED, VALUE, count(*) as cnt from
			(
				SELECT DATE_FORMAT(ADDED, '%Y-%m-%d') as dt, ID, VALUE, ADDED, VALUE_ID FROM phistory 
				WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd'
			) as foo
			group by dt, value order by dt, VALUE
		";

		# code...
		break;	


	case 4:
		$sql = "
			select dt as ADDED, VALUE, count(*) as cnt from
			(
				SELECT DATE_FORMAT(ADDED, '%Y-%m') as dt, ID, VALUE, ADDED, VALUE_ID FROM phistory 
				WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd'
			) as foo
			group by dt, value order by dt, VALUE
		";
		break;


	case 5:
		$sql = "
			select dt as ADDED, VALUE, count(*) as cnt from
			(
				SELECT DATE_FORMAT(ADDED, '%Y') as dt, ID, VALUE, ADDED, VALUE_ID FROM phistory 
				WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd'
			) as foo
			group by dt, value order by dt, VALUE
		";
		break;


	default:
		# code...
		break;
}


$ph=SQLSelect($sql);
//var_dump($ph);

//$ph=SQLSelect("SELECT * FROM phistory WHERE VALUE_ID = {$pvalue['ID']} order by ADDED");
//var_dump($ph);



$xCategories = array();
$xValues = array();

$res = array();
foreach ($ph as $value)
{
	if (!in_array($value['ADDED'], $xCategories)) $xCategories[] = $value['ADDED'];
	if (!in_array($value['VALUE'], $xValues)) $xValues[] = $value['VALUE'];
	
	$res[$value['ADDED']][$value['VALUE']] = (int)$value['cnt'];
}

$tt = array();
foreach ($xValues as $val)
{
	foreach ($xCategories as  $date)
	{
		
		$tt[$val][] = isset($res[$date][$val]) ? $res[$date][$val] : null;
	}
}

//var_dump($xCategories);
//var_dump($xValues);
//var_dump($res);
//var_dump($tt);

/********************************************************************/

$chart = new Highchart();

$chart->chart->renderTo = "container";
$chart->chart->type = "bar";
$chart->title->text = "Historic World Population by Region";
$chart->subtitle->text = "Source: Wikipedia.org";
$chart->xAxis->categories = $xCategories;

$chart->xAxis->title->text = null;
$chart->yAxis->min = 0;
$chart->yAxis->title->text = "Population (millions)";
$chart->yAxis->title->align = "high";

$chart->tooltip->formatter = new HighchartJsExpr(
    "function() {
    return '' + this.series.name +': '+ this.y +' millions';}");

$chart->plotOptions->bar->dataLabels->enabled = 1;
$chart->legend->layout = "vertical";
$chart->legend->align = "right";
$chart->legend->verticalAlign = "top";
$chart->legend->x = - 100;
$chart->legend->y = 100;
$chart->legend->floating = 1;
$chart->legend->borderWidth = 1;
$chart->legend->backgroundColor = "#FFFFFF";
$chart->legend->shadow = 1;
$chart->credits->enabled = false;


	foreach ($xValues as $value) 
	{
		$chart->series[] = array(
		    'name' => $value,
		    'data' => $tt[$value],
		);
	}

	/*
var_dump("*************************");
var_dump($tt[1]);	

$chart->series[] = array(
		    'name' => 1,
		    'data' => array(1),
		);

$chart->series[] = array(
		    'name' => 2,
		    'data' => array(1),
		);
*/


}

if ($graphType == 3)
{

$sql = "
	SELECT * FROM phistory WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd' order by ADDED
";
//var_dump($sql);


$ph=SQLSelect($sql);
//var_dump($ph);

//$ph=SQLSelect("SELECT * FROM phistory WHERE VALUE_ID = {$pvalue['ID']} order by ADDED");
//var_dump($ph);

$res = array();
for ($i = 0; $i < count($ph); $i++)
{
	$v = $ph[$i]['VALUE'];
	//if (isset($data[$i+1]) && $data[$i+1]['val'] !== $data[$i]['val'])
	if (isset($ph[$i+1]))
	{
		if (!isset($res[$v])) $res[$v] = 0;
		$res[$v] += strtotime($ph[$i+1]['ADDED']) - strtotime($ph[$i]['ADDED']);
	}
	else
	{
		if (!isset($res[$v])) $res[$v] = 0;
		$res[$v] += strtotime($dateEnd) - strtotime($ph[$i]['ADDED']);
	}
}

$periodSec = strtotime($dateEnd) - strtotime($dateStart);

$divider = array(1,60, 3600, 86400, 86400*30, 86400 * 365);

var_dump($res);
foreach ($res as $k =>$v)
{
	echo $k . "|" . (float)$v / $divider[$aggregationType]. "|||" .(float)$v / $periodSec * 100  . "<br>" .  PHP_EOL;
}

//die('asd');




/********************************************************************/

$chart = new Highchart();

$chart->chart->renderTo = "container";
$chart->chart->plotBackgroundColor = null;
$chart->chart->plotBorderWidth = null;
$chart->chart->plotShadow = false;
$chart->title->text = "Browser market shares at a specific website, 2010";

$chart->tooltip->formatter = new HighchartJsExpr(
    "function() {
    return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';}");

$chart->plotOptions->pie->allowPointSelect = 1;
$chart->plotOptions->pie->cursor = "pointer";
$chart->plotOptions->pie->dataLabels->enabled = 1;
$chart->plotOptions->pie->dataLabels->color = "#000000";
$chart->plotOptions->pie->dataLabels->connectorColor = "#000000";

$chart->plotOptions->pie->dataLabels->formatter = new HighchartJsExpr(
    "function() {
    return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %'; }");

$data = array();

foreach ($res as $k =>$v)
{
	$data[] = array((string)$k, (float)$v / $periodSec * 100 );
}

$chart->series[] = array(
    'type' => "pie",
    'name' => "Browser share",
    'data' => $data,
);


}



if ($graphType == 4)
{

$sql = "
	select VALUE, cnt, cnt / (select count(*) from phistory) * 100 as percent from
	(
		SELECT VALUE, count(*) as cnt FROM phistory WHERE VALUE_ID = {$pvalue['ID']} and ADDED >= '$dateStart' and ADDED < '$dateEnd' group by VALUE
	) as foo
";



$ph=SQLSelect($sql);
//var_dump($ph);

//$ph=SQLSelect("SELECT * FROM phistory WHERE VALUE_ID = {$pvalue['ID']} order by ADDED");
//var_dump($ph);




/********************************************************************/

$chart = new Highchart();

$chart->chart->renderTo = "container";
$chart->chart->plotBackgroundColor = null;
$chart->chart->plotBorderWidth = null;
$chart->chart->plotShadow = false;
$chart->title->text = "Browser market shares at a specific website, 2010";

$chart->tooltip->formatter = new HighchartJsExpr(
    "function() {
    return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';}");

$chart->plotOptions->pie->allowPointSelect = 1;
$chart->plotOptions->pie->cursor = "pointer";
$chart->plotOptions->pie->dataLabels->enabled = 1;
$chart->plotOptions->pie->dataLabels->color = "#000000";
$chart->plotOptions->pie->dataLabels->connectorColor = "#000000";

$chart->plotOptions->pie->dataLabels->formatter = new HighchartJsExpr(
    "function() {
    return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %'; }");

$data = array();
foreach ($ph as $value)
{
	$data[] = array($value['VALUE'],(float)$value['percent'] );
}

$chart->series[] = array(
    'type' => "pie",
    'name' => "Browser share",
    'data' => $data,
);


}



?>

<html>
    <head>
        <title>Basic Line</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <?php $chart->printScripts(); ?>
    </head>
    <body>
        <div id="container"></div>
        <script type="text/javascript"><?php echo $chart->render("chart1"); ?></script>
    </body>
</html>