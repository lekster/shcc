<?php

class AopTimerAspect implements aop\advice\IAdvice {

    public function __construct () 
    {
        
    }

    public function getKindOfAdvice() 
    {
        return AOP_KIND_AROUND;
    }

    public function __invoke (\AopJoinpoint $aop) 
    {
        $time = microtime(true);
        $aop->process();
        $time = microtime(true) - $time;

        if ($aop->getKindOfAdvice() & AOP_KIND_METHOD) {
            $call = $aop->getClassName()."::".$aop->getMethodName();
        } else {
            $call = $aop->getFunctionName();
        }
        echo sprintf ("%s en %f", $call, $time) . PHP_EOL;
    }
}