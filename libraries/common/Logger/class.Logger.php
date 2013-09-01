<?php

require_once 'libraries/common/Logger/interface.Loggable.php';
require_once 'libraries/common/Logger/Graylog/GELFMessage.php';
require_once 'libraries/common/Logger/Graylog/GELFMessagePublisher.php';

/**
 * Имплементация механизма логирования
 *
 * @package src/implementation
 */

class Immo_MobileCommerce_Logger implements Immo_MobileCommerce_Loggable
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

    private static $levelsmap = array(
    	'debug' => self::Debug,
    	'info' 	=> self::Info,
    	'error' => self::Error,
    	'warn'  => self::Warn,
    	'fatal' => self::Fatal
    );

    private static $graylogLevelMaps = array
    (
        self::Debug => GELFMessage::DEBUG,
        self::Info  => GELFMessage::INFO,
        self::Error => GELFMessage::ERROR,
        self::Fatal => GELFMessage::ALERT,
        self::Warn  => GELFMessage::WARNING,
    );

    private $graylogBlackListHosts = array
    (
        'fenrir.immo',
        'embla.immo',
    );

    private $realFileName = null;

    private $graylogHost = 'vps8242.mtu.immo';
    private $hostName = '';
    private $hostNameForGraylog = '';
    private $graylogPublisher;

    public function __construct($filename = false, $level = false, $tracedepth = false, $graylogHost = null)
    {
        $this->Level  = $level;
        $this->Format = self::DEFAULT_FORMAT;
        $this->Filename = $filename;
        if ($tracedepth) $this->setTraceDepth($tracedepth);
        $this->hostName = trim(`hostname`);
        $this->hostNameForGraylog = str_replace(".", "_", $this->hostName);
        //if (!is_null($graylogHost)) $this->graylogHost = $graylogHost;
        //$this->graylogPublisher = new GELFMessagePublisher($this->graylogHost);
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
    public function debug($message, $obj = false, $facility = null)
    {
        $this->log(self::Debug, $message, $obj, $facility);
    }

    /**
     * Report an error message.
     * @param string $message log message
     * 
     */
    public function error($message, $obj = false, $facility = null)
    {
        $this->log(self::Error, $message, $obj, $facility);
    }

    /**
     * Report a fatal message.
     * @param string $message log message
     *
     * @param value
     */
    public function fatal($message, $obj = false, $facility = null)
    {
        $this->log(self::Fatal, $message, $obj, $facility);
    }

    /**
     * Report an info message.
     * @param string $message log message
     *
     *
     * @param message
     */
    public function info($message, $obj = false, $facility = null)
    {
        $this->log(self::Info, $message, $obj, $facility);
    }

    /**
     * Report a warning message.
     * @param string $message log message
     *
     *
     * @param message
     */
    public function warn($message, $obj = false, $facility = null)
    {
        $this->log(self::Warn, $message, $obj, $facility);
    }

    /**
     * @param int $level Use class constants to set level
     * @param string $message
     */
    public function log($level, $message, $obj = false, $facility = null)
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

            //send to graylog
            if (!in_array($this->hostName, $this->graylogBlackListHosts))
            { 
                $runScriptName = (isset($_SERVER['SCRIPT_FILENAME'])) ? basename($_SERVER['SCRIPT_FILENAME']) : 'unknown';
                //$runScriptName = (isset($_SERVER["argv"][0])) ? basename($_SERVER["argv"][0]) : 'unknown';        
                $runScriptName .= "|" . posix_getpid() . "|";
                //var_dump($runScriptName);
                $glm = new GELFMessage();
                $glm->setShortMessage($runScriptName . $message);
                $glm->setFullMessage($this->renderObject($data['object']));
                $glm->setHost($this->hostName);
                //$glm->setHost($this->hostNameForGraylog);
                $glm->setLevel(self::$graylogLevelMaps[$level]);
                $glm->setFile($data['trace']['file']);
                $glm->setLine($data['trace']['line']);
                $glm->setTimestamp(microtime(1));
                if (!is_null($facility)) $glm->setFacility($facility); else $glm->setFacility(substr(strrchr(GIRAR_BASE_DIR, "/"), 1));
                //$this->graylogPublisher->publish($glm);
            }
            //write to log file
            $fp = @fopen( $this->Filename, "a" );
            if(is_resource($fp))
            {
                $text = (strcmp($this->Format, self::DEFAULT_FORMAT) == 0)?$this->formatDefault($data):$this->format($data);
                @fwrite( $fp, $text."\n" );
                @fclose( $fp );
            } else {
                //mail('asmirnov@immo.ru', 'mobile-commerce: class.Logger.php', 'Failed to open log: '.$this->Filename);
            }
        }
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
