<?php

require_once 'pbr-lib-common/src/Logger/interface.Loggable.php';
require_once 'pbr-lib-common/src/Logger/log4php/Logger.php';

/**
 * Имплементация механизма логирования
 *
 * @package src/implementation
 */

class Immo_MobileCommerce_LoggerExt implements Immo_MobileCommerce_Loggable
{
	static $config = null;
    static $logparamsmap = null;

    const DEFAULT_FORMAT = '%d %t [%p] %F:%L %C::%M >>> %m %O';
    const DEFAULT_FILENAME = '/var/log/sender/common.log';

    private $logEventsListeners = array();

    private $Strings = array(
        0 => 'DEBUG',
        1 => 'INFO',
        2 => 'WARN',
        3 => 'ERROR',
        4 => 'FATAL'
    );

    private $Filename;
    private $Format;
    private $Level;
    private $TraceDepth = 2;
    protected $_log4phpLogger = null;

    private static $levelsmap = array(
    	'debug' => self::Debug,
    	'info' 	=> self::Info,
    	'error' => self::Error,
    	'warn'  => self::Warn,
    	'fatal' => self::Fatal
    );

	private $realFileName = null;

    public function __construct($filename = false, $level = false, $tracedepth = false, $config = array())
    {
        $this->Level  = $level;
        $this->Format = self::DEFAULT_FORMAT;
        $this->Filename = $filename;
        if ($tracedepth) $this->setTraceDepth($tracedepth);

        $defaultConfig =    array
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
                                            'file' => $filename,
                                            'append' => true
                                        )
                                    )
                                )
                            );

        if (empty($config))
            $config = $defaultConfig;

        // Tell log4php to use our configuration file.
        Logger::configure($config);
         
        // Fetch a logger, it will inherit settings from the root logger
        $this->_log4phpLogger = Logger::getLogger('myLogger');
        // Start logging
        //$log->trace("My first message."); // Not logged because TRACE < WARN

    }


	//public static function getInstance(Immo_MobileCommerce_Config $config)
	public static function getInstance()
    {
       	if (is_null(self::$config))
        {
            self::$config = Immo_MobileCommerce_ServiceLocator::getInstance()->getConfig();
            $loggerSection = self::$config->getSection('Logger');
            self::$logparamsmap = $loggerSection['LogParamsMap'];
        }

        $key = self::getRequesterClassName();

        if (key_exists($key, self::$logparamsmap))
        {
            $localParams = self::$logparamsmap[$key];
        } else
        {
            $localParams = self::$logparamsmap['<default>'];
        }

        $logger = new Immo_MobileCommerce_Logger($localParams['LogFile'], self::$levelsmap[$localParams['LogLevel']]);

        return $logger;
    }

    protected static function getRequesterClassName()
    {
        $trace = debug_backtrace();

        $className = false;

        foreach ($trace as $key => $entry)
        {
            if (isset($trace[$key]['class']) && $trace[$key]['class']!=__CLASS__)
            {
                $className = $trace[$key]['class'];
                unset($trace);
                return $className;
            }
        }

        return '<default>';
    }


    /**
     * @param int $value Use class constants to set level
     */
    public function setLevel($value)
    {
        if(!key_exists($value, $this->Strings))
        {
            throw new Immo_Exceptions_WrongValueException($value, array_keys($this->Strings));
        }

        $this->Level = $value;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->Level;
    }

    /**
     * @param string $value
     */
    public function setFormat($value)
    {
        if(strcmp($this->Filename, self::DEFAULT_FILENAME) == 0)
        {
            throw new Immo_Exceptions_CommonException('It is not allowed to set format for default log file!');
        }

        $this->Format = strval($value);
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->Format;
    }

    /**
     * @param string $value
     */
    public function setFilename($value)
    {
        //чтобы realpath не возвращал ошибку для несуществующих файлов
        @touch($value);
        $realPath = realpath($value);
        if($realPath === false)
        {
            throw new Immo_Exceptions_CommonException('File \''.$value.'\' doesn\'t seem to exist!!!');
        }

        if(strcmp($realPath, self::DEFAULT_FILENAME) == 0)
        {
            $this->Filename = self::DEFAULT_FILENAME;
            $this->Format = self::DEFAULT_FORMAT;
        }
        else
        {
            $this->Filename = $value;
        }

        @chmod($value,0664);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->Filename;
    }

    /**
     * Устанавливает глубину трейса для записи в лог
     *
     * @param int $value. Should be greater than 1
     * @return bool
     */
    public function setTraceDepth($value)
    {
        $value = intval($value);
        if($value < 2) return false;

        $this->TraceDepth = $value;
        return true;
    }

    /**
     * @return  int $value
     */
    public function getTraceDepth()
    {
        return $this->TraceDepth;
    }


    /**
     * Report a debug message.
     * @param string $message log message
     */
    public function debug($message, $obj = false)
    {
        $logMessage = $this->formatLogMessage(self::Debug, $message, $obj);
        $this->_log4phpLogger->debug($logMessage);    
        //$this->log(self::Debug, $message, $obj);
    }

    /**
     * Report an error message.
     * @param string $message log message

     */
    public function error($message, $obj = false)
    {
        $logMessage = $this->formatLogMessage(self::Error, $message, $obj);
        $this->_log4phpLogger->error($logMessage);    
        //$this->log(self::Error, $message, $obj);
    }

    /**
     * Report a fatal message.
     * @param string $message log message
     *
     * @param value
     */
    public function fatal($message, $obj = false)
    {
        $logMessage = $this->formatLogMessage(self::Fatal, $message, $obj);
        $this->_log4phpLogger->fatal($logMessage); 

        //$this->log(self::Fatal, $message, $obj);
    }

    /**
     * Report an info message.
     * @param string $message log message
     *
     *
     * @param message
     */
    public function info($message, $obj = false)
    {
        $logMessage = $this->formatLogMessage(self::Info, $message, $obj);
        $this->_log4phpLogger->info($logMessage); 
        //$this->log(self::Info, $message, $obj);
    }

    /**
     * Report a warning message.
     * @param string $message log message
     *
     *
     * @param message
     */
    public function warn($message, $obj = false)
    {
        $logMessage = $this->formatLogMessage(self::Warn, $message, $obj);
        $this->_log4phpLogger->warn($logMessage); 
        //$this->log(self::Warn, $message, $obj);
    }

    /**
     * @param int $level Use class constants to set level
     * @param string $message
     */
    public function log($level, $message, $obj = false)
    {

        if(!key_exists($level, $this->Strings))
        {
            throw new Immo_Exceptions_WrongValueException($level, array_keys($this->Strings));
        }

        if ( $level >= $this->Level )
        {
            $fullTrace = debug_backtrace();
            $trace = $fullTrace[$this->TraceDepth - 1];
            $parentTrace = (isset( $fullTrace[$this->TraceDepth] ))?$fullTrace[$this->TraceDepth]:
            array('function' => 'main', 'class' => '');

            $data = array
            (
            'trace'         => $trace,
            'parentTrace'   => $parentTrace,
            'message'       => $message,
            'object'        => $obj,
            'level'         => $this->Strings[$level]
            );

            $level = strtolower($this->Strings[$level]);
            $text = (strcmp($this->Format, self::DEFAULT_FORMAT) == 0)?$this->formatDefault($data):$this->format($data);
            $this->_log4phpLogger->$level($text);

            /*
            $fp = @fopen( $this->Filename, "a" );

            if(is_resource($fp))
            {
                $text = (strcmp($this->Format, self::DEFAULT_FORMAT) == 0)?$this->formatDefault($data):$this->format($data);
                @fwrite( $fp, $text."\n" );
                @fclose( $fp );
            } else {
                mail('asmirnov@immo.ru', 'mobile-commerce: class.Logger.php', 'Failed to open log: '.$this->Filename);
            }
            */
        }
    }


    protected function formatLogMessage($level, $message, $obj = false)
    {
        $text = "";
        if(!key_exists($level, $this->Strings))
        {
            throw new Immo_Exceptions_WrongValueException($level, array_keys($this->Strings));
        }

        if ( $level >= $this->Level )
        {
            $fullTrace = debug_backtrace();
            $trace = $fullTrace[$this->TraceDepth - 1];
            $parentTrace = (isset( $fullTrace[$this->TraceDepth] ))?$fullTrace[$this->TraceDepth]:
            array('function' => 'main', 'class' => '');

            $data = array
            (
            'trace'         => $trace,
            'parentTrace'   => $parentTrace,
            'message'       => $message,
            'object'        => $obj,
            'level'         => $this->Strings[$level]
            );

            $text = (strcmp($this->Format, self::DEFAULT_FORMAT) == 0)?$this->formatDefault($data):$this->format($data);

        }
        return $text;    
    }

    protected function format( $data )
    {
        $result = array();

        $position = 0;
        while(($found = strpos($this->Format, '%', $position)) !== false)
        {
            if($found > $position)
            {
                $result[] = substr($this->Format, $position, $found - $position);
            }

            if(strlen($this->Format) == ($found + 1))
            {
                $result[] = "%";
                $position = $found + 1;
            }
            else
            {
                $token = $this->Format[$found + 1];
                switch($token)
                {
                    case "d":
                        $result[] = date('d.M.Y H:i:s');
                        break;

                    case "t":
                        $result[] = getmypid();
                        break;

                    case "p":
                        $result[] = $data['level'];
                        break;

                    case "F":
                        $result[] = $data['trace']['file'];
                        break;

                    case "L":
                        $result[] = $data['trace']['line'];
                        break;

                    case "m":
                        $result[] = $data['message'];
                        break;

                    case "O":
                        $result[] = $this->renderObject($data['object']);
                        break;

                    case "C":
                        $result[] = $data['parentTrace']['class'];
                        break;

                    case "M":
                        $result[] = $data['parentTrace']['function']."()";
                        break;

                    case '%':
                        $result[] = '%';
                        break;
                    default:
                        $result[] = "%$token";
                }

                $position = $found + 2;
            }
        }

        if($position < strlen($this->Format)) $result[] = substr($this->Format, $position);
        return implode('', $result);
    }

    private function renderObject($object)
    {
        $text = $object;

        /**
         * to avoid endless var_export loop lead to uncatchable fatal error
         */
        if ($object instanceof Exception )
        {
            $a = array(
                'message' => $object->getMessage(),
                'file' => $object->getFile(),
                'line' => $object->getLine(),
                'code' => $object->getCode()
            );
            $text = var_export($a, true);
        } else
        {
            $text = ($object !== false) ? var_export($object, true) : '';
        }

        if(!empty($text))
        {
            $text = "\n\t".str_replace("\n", "\n\t", $text);
        }

        return $text;
    }

    private function formatDefault( $data )
    {
		if(!isset($data['parentTrace']['class']))
			$data['parentTrace']['class']='';
        //'%d %t [%p] %F:%L %C::%M >>> %m %O'
        list($ms, $stamp) = explode(' ', microtime());
        $ms = strstr($ms, '.');

        $parts = array(
        date('d.M.Y H:i:s').$ms,
        getmypid(),
        "[{$data['level']}]",
        "{$data['trace']['file']}:{$data['trace']['line']}",
        "{$data['parentTrace']['class']}::{$data['parentTrace']['function']}()",
        "\n>>>",
        $data['message'],
        $this->renderObject($data['object']),
        "\n",
        );

        return implode(' ', $parts);
    }
}
