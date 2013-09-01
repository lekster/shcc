<?php

require_once __DIR__ . '/../../src/aop/Aop.php';

require_once __DIR__ . '/../../src/aop/advice/IAdvice.php';


class AopTimerAspect implements aop\advice\IAdvice {

    private $_logger = null;

    public function __construct ($logger = null) {
        $this->_logger = $logger;
    }

    public function getKindOfAdvice() {
        return AOP_KIND_AROUND;
    }

    public function __invoke (\AopJoinpoint $aop) {
        $time = microtime(true);
        $aop->process();
        $time = microtime(true)-$time;
        if ($aop->getKindOfAdvice() & AOP_KIND_METHOD) {
            $call = $aop->getClassName()."::".$aop->getMethodName();
        } else {
            $call = $aop->getFunctionName();
        }
        $log = sprintf ("%s en %f", $call, $time);
        if ($this->_logger!=null) {
            $this->_logger->addInfo($log);
        } else {
            echo $log . PHP_EOL;
        }
    }

}


class Aop1Aspect implements aop\advice\IAdvice {

    
    public function getKindOfAdvice() {
        return AOP_KIND_AFTER;
    }

    public function __invoke (\AopJoinpoint $aop) {
       echo "After1" . PHP_EOL;
    }

}

class Aop2Aspect implements aop\advice\IAdvice {

    
    public function getKindOfAdvice() {
        return AOP_KIND_AFTER;
    }

    public function __invoke (\AopJoinpoint $aop) {
       echo "After2" . PHP_EOL;
    }

}


class test
{

	public function run()
	{

		echo "run" . PHP_EOL;
	}



}

echo "--------------RUNNING WITHOUT AOP----------------" . PHP_EOL;

$t = new test();
$t->run();

//AOP
echo "--------------AOP----------------" . PHP_EOL;
$advice = new AopTimerAspect();

$aop = new aop\Aop();
//1
$aop->add('test->run()', $advice);

//2
//закомментить выше //1 перед использованием
//$aop->add('test->run()', new Aop1Aspect());
//$aop->add('test->run()', new Aop2Aspect());

$t = new test();
$t->run();
