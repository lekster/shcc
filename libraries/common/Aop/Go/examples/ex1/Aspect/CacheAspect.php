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
 * Cache aspect
 */
class CacheAspect implements Aspect
{
    /**
     * Cacheable methods
     *
     * @param MethodInvocation $invocation Invocation
     *
     * @Around("@annotation(Annotation\Cacheable)")
     */
    public function aroundCacheable(MethodInvocation $invocation)
    {
        static $memoryCache = array();
        $time  = microtime(true);

        $obj   = $invocation->getThis();
        $class = is_object($obj) ? get_class($obj) : $obj;
        $key   = md5($class . ':' . $invocation->getMethod()->name . ":" . $invocation->getArguments());
        if (!isset($memoryCache[$key])) {
            $memoryCache[$key] = $invocation->proceed();
            echo 'key not found in cache call original method, return - ' . $memoryCache[$key];
        }
        else
        {
            echo 'key found in cache, return - ' . $memoryCache[$key];
        }

        echo "Take ", sprintf("%0.3f", (microtime(true) - $time) * 1e3), "ms to call method<br>", PHP_EOL;
        return $memoryCache[$key];
    }
}
