<?php
/**
 * Go! OOP&AOP PHP framework
 *
 * @copyright     Copyright 2012, Lissachenko Alexander <lisachenko.it@gmail.com>
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

require_once 'pbr-lib-common/src/Config/class.Config.php';
require_once 'pbr-lib-common/src/class.Exception.php'; 
require_once  'pbr-lib-common/src/Aop/Go/Core/AspectKernel.php';
require_once __DIR__ . '/DemoAspectKernel.php';
 
use Doctrine\Common\Annotations\AnnotationRegistry;

// Initialize demo aspect container
DemoAspectKernel::getInstance()->init(array(
    // Configuration for autoload namespaces
    'autoload' => array(
        'Go'               => realpath('pbr-lib-common/src/Aop/'),
        'TokenReflection'  => realpath('pbr-lib-common/src/'),
        'Doctrine\\Common' => realpath('pbr-lib-common/src/'),
        'Dissect'          => realpath('pbr-lib-common/src/'),
        //'Aspect'          => realpath(__DIR__ . '/Aspect/'),
    ),
    // Default application directory
    'appDir' =>  __DIR__ . '/',
    // Cache directory for Go! generated classes
    //'cacheDir' => __DIR__ . '/cache/',
    'cacheDir' => null,

    // Include paths for aspect weaving
    'includePaths' => array(
       //__DIR__ . '/Aspect/',

        ),
    //'debug' => true
));

//for cache use
AnnotationRegistry::registerFile(__DIR__ . '/Annotation/Cacheable.php');