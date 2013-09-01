<?php

return array(

	'extends' => array(
		//'default.php',
	),

    'Implementations' => array(
        'TimerAspect' => array(
            '<default>' => array(
                'ImplementationClassFilepath' => __DIR__ . '/TimerAspect.php',
                'ImplementationClassName'     => 'AopTimerAspect',
            ),
        ),
    ),



    'IOC' => array(
        
        'TimerAspect1234' => array(
            '<default>' => array(
                'Implementation' => 'TimerAspect',
                'ConstructMethod' => '#',
                'ConstructParams' => array(),
                /*'InitMethod' => 'setAddParams',
                'InitParams' => array(
                    array("immo" => 'devino_immo'),
                ),*/
                'IsPersistent' => true,
            )
        ),
    ),




);
