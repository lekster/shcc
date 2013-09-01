<?php

require_once 'pbr-lib-common/src/Logger/interface.Loggable.php';
require_once 'pbr-lib-common/src/Logger/log4php/Logger.php';

/**
 * Имплементация механизма логирования
 *
 * @package src/implementation
 */

class Immo_MobileCommerce_Log4Php
{
    static $_logger = null;

	public static function getInstance($config = array(), $loggerName = 'myLogger')
    {

        $defaultConfig =    
        array
        (
            'rootLogger' => array
            (
                'appenders' => array('default'),
                'level value' => "DEBUG",
            ),
            'appenders' => array
            (
                'default' => array
                (
                    'class' => 'LoggerAppenderFile',
                    'layout' => array
                    (
                        'class' => 'LoggerLayoutSimple'
                    ),
                    'params' => array
                    (
                        'file' => '/tmp/log.log',
                        'append' => true
                    )
                )
            )
        );

        if (empty($config))
            $config = $defaultConfig;
        Logger::configure($config);
        return Logger::getLogger($loggerName);
    }

  
}
