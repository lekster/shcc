<?php


include '../htdocs/bootstrap.php';
chdir(GIRAR_BASE_DIR);
//chdir('../../../../../');
//echo `pwd`;

require_once 'pbr-lib-common/src/Aop/AopPHP/src/aop/AopExt.php';
require_once __DIR__ . '/TimerAspect.php';
require_once 'pbr-lib-common/src/Config/class.Config.php';
require_once 'pbr-lib-common/src/class.Exception.php';


class test
{

	public function run()
	{

		echo "run" . PHP_EOL;
	}
}


$t = new test();
$t->run();

//AOP
echo "--------------AOP----------------" . PHP_EOL;
$config = new Immo_MobileCommerce_Config(__DIR__ . '/conf.php');
$confArr = array(
    'test->run()' => 'TimerAspect1234', 
);

$aop = new aop\AopExt($config);
$aop->setByConfArray($confArr);

$t = new test();
$t->run();
