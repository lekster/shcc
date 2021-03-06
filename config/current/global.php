<?php

return array(

	 /*'extends' => array(
         '../common.php',
	 ),*/

	  'Datasource' => array
	  (

        'doctrineDatasourceSmsDb' => array(
            'driver' => 'pdo_pgsql',
            'user' => 'inform',
            'password'=>'l!j@cneg',
            'host'=>'donar.immo',
            'port'=> 5432,
            'dbname'=>'pbr-serv-sms',
        ),  

       
        'DoctrineDatasourceDb' => array(
            'driver'   => 'pdo_mysql',
            'user'     => 'worker',
            'password' => 'worker',
            'dbname'   => 'db_terminal',
            'host' => '10.192.163.44',
        ),


        /*'doctrineDatasourceRequestDb' => array(
            'driver' => 'pdo_pgsql',
            'user' => 'inform',
            'password'=>'l!j@cneg',
            'host'=>'donar.immo',
            'port'=> 5432,
            'dbname'=>'pbr-serv-sms-sender',
        ),*/    
    ),   



      'Global' => array
      (
            'BackupDir' => '/home/asmirnov/temp/backup/',
            'CacheDir' => '/home/asmirnov/temp/cache/',
            'CmsDir' => '/home/asmirnov/temp/cms/',
            'TextsDir' => '/home/asmirnov/temp/texts/',
            'SoundsDir' => '/home/asmirnov/temp/sounds/',

            'BaseDataDir' => '/tmp/majordomo/',
            'DataDir' => array
            (
                'BackupDir' => '1/',
                'CacheDir' => 'cache/',
                'CmsDir' => 'cms/',
                'TextsDir' => 'texts/',
                'SoundsDir' => 'sounds/',      
            ),

      ),

	  'IOC' => array(

            'Logger' => array(
                '<default>' => array(
                    'Implementation' => 'Logger',
                    'ConstructMethod' => '#',
                    'ConstructParams' => array("/tmp/1.log", 0),
                    'IsPersistent' => true,
                )
            ),

            'CronLock' => array(
                '<default>' => array(
                    'Implementation' => 'CronLock',
                    'ConstructMethod' => '#',
                    'ConstructParams' => array(
                        '/tmp/%s.%s.%s.%s.lock',
                        '##Logger',
                        "asmirnov@immo.ru",
                        true,
                        "720",
                    ),
                    'IsPersistent' => true,
                )
            ),

            
           'SmsSenderReturnConnector' => array(
            '<default>' => array(
                'Implementation' => 'AmqpConnector',
                'ConstructMethod' => '#',
                'ConstructParams' => array(
                    'vps8097.mtu.immo',                                                 // server
                    'guest',                                                            // login
                    'guest',                                                            // password
                    null,                                                               // heartbeat
                    'pbr-wserv-sms-rpc/config/xsd/mq_message.xsd',                       // messageXsd
                    'pbr-wserv-sms-rpc/config/xsd/smsStatusCallback.xsd',                        // dataXsd
                    'SmsSender-CommonSendqwdwqdwqdq',                                            // channelName
                ),
                'IsPersistent' => false,
             ),
            ),

           'AnalyticsStatsdSender' => array(
			'<default>' => array(
				'Implementation' => 'AnalyticsStatsdSender',
				'ConstructMethod' => '#',
				'ConstructParams' => array('192.168.1.120', 8125),
				'IsPersistent' => true,
				),
			),


           'SmsInfocallerExchangeConnector' => array(
            '<default>' => array(
                'Implementation' => 'AmqpConnector',
                'ConstructMethod' => '#',
                'ConstructParams' => array(
                    //'vps8097.mtu.immo',                                                 // server
                    //'guest',                                                            // login
                    //'guest',
                    'vps8191.mtu.immo',                                                 // server
                    'inform',                                                            // login
                    'l!j@cneg',                                                            // password
                    null,                                                               // heartbeat
                    'pbr-wserv-sms-rpc/config/xsd/mq_message.xsd',                       // messageXsd
                    'pbr-wserv-sms-rpc/config/xsd/smsObj.xsd',                        // dataXsd
                    'InfoCaller-CommonSend',                                            // channelName
                ),
                'IsPersistent' => false,
             ),
            ),


        ),


        'Implementations' => array(
        'Logger' => array(
                '<default>' => array(
                        'ImplementationClassFilepath' => 'libraries/common/Logger/class.Logger.php',
                        'ImplementationClassName'     => 'Immo_MobileCommerce_Logger',
                ),
        ),

        'Log4Php' => array(
                '<default>' => array(
                        'ImplementationClassFilepath' => 'libraries/common/Logger/class.Log4Php.php',
                        'ImplementationClassName'     => 'Immo_MobileCommerce_Log4Php',
                ),
        ),

        'CronLock' => array(
            '<default>' => array(
                'ImplementationClassFilepath' => 'libraries/common/CronLock/class.CronLock.php',
                'ImplementationClassName'     => 'Immo_MobileCommerce_CronLock',
            ),
        ),


        'Event' => array(
            '<default>' => array(
                'ImplementationClassFilepath' => 'pbr-lib-common/src/Event/class.EventHandler.php',
                'ImplementationClassName'     => 'PBR_LIB_COMMON_EventHandler',
            ),
        ),

        'ImmoPlatformSms' => array(
            '<default>' => array(
                'ImplementationClassFilepath' => 'pbr-serv-sms-sender/Src/Transporter/ImmoPlatformSms.php',
                'ImplementationClassName'     => 'Src\\Transporter\\ImmoPlatformSms',
            ),
        ),

        'AmqpConnector' => array(
            '<default>' => array(
                'ImplementationClassFilepath' => 'pbr-lib-common/src/MQ/class.AmqpConnector.php',
                'ImplementationClassName' => 'Immo_MQ_AmqpConnector',
            ),
        ),

        'AnalyticsStatsdSender' => array(
            '<default>' => array(
                'ImplementationClassFilepath' => 'libraries/common/Analytics/Statsd/class.AnalyticsStatsdSender.php',
                'ImplementationClassName' => 'PBR_Analytics_Statsd_Sender',
            ),
        ),

        
    ),


);