http://logging.apache.org/log4php/docs/configuration.html



    Logger::configure(array(
    'rootLogger' => array(
    'appenders' => array('default'),
    ),
    'appenders' => array(
    'default' => array(
    'class' => 'LoggerAppenderFile',
    'layout' => array(
    'class' => 'LoggerLayoutSimple'
    ),
    'params' => array(
    'file' => '/var/log/my.log',
    'append' => true
    )
    )
    )
    ));



    include('log4php/Logger.php');
Logger::configure('config.xml');
 
/**
* This is a classic usage pattern: one logger object per class.
*/
class Foo
{
/** Holds the Logger. */
private $log;
 
/** Logger is instantiated in the constructor. */
public function __construct()
{
// The __CLASS__ constant holds the class name, in our case "Foo".
// Therefore this creates a logger named "Foo" (which we configured in the config file)
$this->log = Logger::getLogger(__CLASS__);
}
 
/** Logger can be used from any member method. */
public function go()
{
$this->log->info("We have liftoff.");
}
}
 
$foo = new Foo();
$foo->go();



// Insert the path where you unpacked log4php
include('log4php/Logger.php');
 
// Tell log4php to use our configuration file.
Logger::configure('config.xml');
 
// Fetch a logger, it will inherit settings from the root logger
$log = Logger::getLogger('myLogger');
 
// Start logging
$log->trace("My first message."); // Not logged because TRACE < WARN