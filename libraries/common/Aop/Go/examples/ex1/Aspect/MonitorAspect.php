<?php

namespace Aspect;

use Go\Aop\Aspect;
use Go\Aop\Intercept\FieldAccess;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Pointcut;

/**
 * Monitor aspect
 */
class MonitorAspect implements Aspect
{

    /**
     * Method that will be called before real method
     *
        * @param MethodInvocation $invocation Invocation
     * @After("execution(public Human->eat(*))")
     */
    public function beforeMethodExecution(MethodInvocation $invocation)
    {
        $obj = $invocation->getThis();
        //var_dump($invocation);
        echo 'Calling After Interceptor for method: ',
             is_object($obj) ? get_class($obj) : $obj,
             $invocation->getMethod()->isStatic() ? '::' : '->',
             $invocation->getMethod()->getName(),
             '()',
             ' with arguments: ',
             json_encode($invocation->getArguments()),
             "<br>\n";
    }

    /**
     * Method that will be called before real method
     *
        * @param MethodInvocation $invocation Invocation
     * @After("execution(public Human->eat(*))")
     */
    public function beforeMethodExecution2(MethodInvocation $invocation)
    {
        $obj = $invocation->getThis();
        //var_dump($invocation);
        echo 'Calling After Interceptor for method: ',
             is_object($obj) ? get_class($obj) : $obj,
             $invocation->getMethod()->isStatic() ? '::' : '->',
             $invocation->getMethod()->getName(),
             '()',
             ' with arguments: ',
             json_encode($invocation->getArguments()),
             "<br>\n";
    }


    /**
     * Method that will be called before real method
     *
        * @param MethodInvocation $invocation Invocation
     * @Around("execution(public Human->sleep(*))")
     */
    public function aroundeMethodExecution(MethodInvocation $invocation)
    {
        $obj = $invocation->getThis();
        //var_dump($invocation);
        
        $time = microtime(true);
        $invocation->proceed();
        $time = microtime(true) - $time;

        echo 'Calling Around Interceptor for method: ',
             is_object($obj) ? get_class($obj) : $obj,
             $invocation->getMethod()->isStatic() ? '::' : '->',
             $invocation->getMethod()->getName(),
             '()',
             ' with arguments: ',
             json_encode($invocation->getArguments()),
             ' Execution Time - ' . $time .
             "<br>\n";
    }
}