<?php

require_once 'pbr-lib-common/src/CronLock/interface.Lockable.php';


class Immo_MobileCommerce_CronLock implements Immo_MobileCommerce_Lockable
{
    protected $_config;
    protected $_logger;

    protected $_notifyEmail;

    protected $_lockName;
    protected $_currentFilename;
    protected $_processExecTimeLimit;
    protected $_stopTime;

    protected $_oldProcessPid = null;
    protected $_secondsOldProcRunning = null;
    protected $_isNeedToKillOldProcess;
    protected $_lockInfo;
    protected $_isUseLockFileModifyTime;

    protected $_linkToGirarBaseDir = null;
    protected $_girarBaseDirContentMarker = null;

    protected $_memoryLimit = null;
    protected $_restartDate;


    //$processExecTimeLimit = -1 - infinity
    public function __construct($lockFileNameTemplate, Immo_MobileCommerce_Loggable $logger, $notifyEmail, $isNeedToKillOldProcess = true, $processExecTimeLimit = 720, $isUseLockFileModifyTime = true,  $maxRunTimeSec = 1800)
    {
        $this->_logger = $logger;
        $this->_notifyEmail = $notifyEmail;
        $this->_isNeedToKillOldProcess = $isNeedToKillOldProcess;
        $this->_processExecTimeLimit = $processExecTimeLimit;
        $this->_isUseLockFileModifyTime = $isUseLockFileModifyTime;

        $backtrace = debug_backtrace();

        $this->_lockName = @basename($backtrace[count($backtrace)-1]['file']);

        if (!isset($this->_lockName)) $this->_lockName = basename($backtrace[count($backtrace)-2]['file']);

        $filename = str_replace('.', '_', $this->_lockName);
        $configFileName = !empty($_SERVER['argv'][1]) ? str_replace("/", "_", $_SERVER['argv'][1]) : '';
        $processNumber = !empty($_SERVER['argv'][2]) ? str_replace("/", "_", $_SERVER['argv'][2]) : '';
        $param4 = !empty($_SERVER['argv'][3]) ? str_replace("/", "_", $_SERVER['argv'][3]) : '';

        $hostname = trim(`hostname`);

        $this->_lockInfo = $hostname . "|" . $filename . "|" . $configFileName . "|" . $processNumber . "|" . $param4;

        $this->_currentFilename = sprintf(
            $lockFileNameTemplate,
            $filename . '_' . md5($this->_lockName),
            $configFileName,
            $processNumber,
            $param4
        );

        $this->_logger->debug("Creating new class for locking file '".$this->_lockName."', generated LockFilename '".$this->_currentFilename."'");

        if (!$this->_isLockFileAccessible())
        {
            $this->_logger->fatal("Seems that cannon get access to file '".$this->_currentFilename."'");
            throw new Exception("Seems that cannon get access to file '".$this->_currentFilename."'");
        }

        $this->_stopTime = $maxRunTimeSec + time() + rand(0, 300); 

        $this->_memoryLimit = $this->memoryLimit();

        $this->storeCurrentReleaseAndInstall();
    }

    protected function _isLockFileAccessible()
    {
        if (!file_exists($this->_currentFilename))
        {
            $this->_logger->debug("File '".$this->_currentFilename."' is not exist.");
            return true;
        }

        $fres = @fopen($this->_currentFilename, 'r');

        if (!$fres)
        {
            $this->_logger->error("File exists. Failed to open for reading.");
            return false;
        }

        @fclose($fres);

        return true;
    }

    public function getOldProcessRunTime()
    {
        return $this->_secondsOldProcRunning;
    }

    public function getOldProcessPid()
    {
        return $this->_oldProcessPid;
    }

    public function isLocked()
    {
        // $this->_logger->debug("Is '".$this->_lockName."' locked?");

        if (!file_exists($this->_currentFilename))
        {
            $this->_logger->debug("File '".$this->_currentFilename."' not found. Consider not locked.");
            return false;
        }

        $process = file_get_contents($this->_currentFilename);

        if (!$process)
        {
            $this->_logger->error("Failed to get contents of file '".$this->_currentFilename."'");
            return false;
        }

        if (trim($process) == 'STOP')
        {
            $this->_logger->info("CRON IS DISABLED");
            echo "CRON IS DISABLED\n";
            return true;
        }

        list($PID, $time, $path) = explode('@@@', $process);

        $this->_logger->debug('Found previous process info: pid='.$PID.', started \''.date('r', $time).'\','.$path);

        $this->_secondsOldProcRunning = time() - intval($time);

        if ($this->_isUseLockFileModifyTime)
        {
            clearstatcache();
            $this->_secondsOldProcRunning = time() - filemtime($this->_currentFilename);
        }

        if (!$PID)
        {
            if ($this->isStopped())
            {
                $this->_logger->info("CRON IS STOPPED BY FILE ".$this->_currentFilename.".stop");
                echo "CRON IS STOPPED BY FILE ".$this->_currentFilename.".stop\n";
                return true;
            }

            $this->_logger->info("Failed to retrieve previous process's PID in file '".$this->_currentFilename."'. Unlocking...");

            $this->unlock();

            return false;
        }

        $this->_oldProcessPid = $PID;

        /**
         * command to know if process is alive
         */
        if (posix_kill($PID, 0))
        {
            // $this->_logger->info("Found running '".$PID."' as posix_kill()");

            if ($this->_processExecTimeLimit && $this->_processExecTimeLimit != -1 && $this->_secondsOldProcRunning > $this->_processExecTimeLimit)
            {
                mail($this->_notifyEmail, 'mobile-commerce-cronlock', 'PROCESS IS RUNING TOOOOO LONG!'. var_export($this->getLockInfo(), true));

                if ($this->_isNeedToKillOldProcess)
                {
                    posix_kill($PID, 9);
                    $this->_logger->info("Process '".$PID."' killed");
                }
            }

            return true;
        }

        /**
         * Process is not exists
         */
        if ($this->isStopped())
        {
            $this->_logger->info("CRON IS STOPPED BY FILE ".$this->_currentFilename.".stop");
            echo "CRON IS STOPPED BY FILE ".$this->_currentFilename.".stop\n";
            return true;
        }

        $this->_logger->debug('Seems that process with PID: '.$PID.' doesn\'t exist. Unlocking...');

        $this->unlock();

        return false;
    }

    public function unlock()
    {
        $this->_logger->debug("LockName: '".$this->_lockName."'. Unlocking the file '".$this->_currentFilename."'");

        @unlink($this->_currentFilename);

        return true;
    }

    public function lock()
    {
        if ($this->isLocked())
        {
            $this->_logger->fatal("File already locked '".$this->_currentFilename."'");
            throw new Exception("File already locked '".$this->_currentFilename."'");

            // TODO: Заменить на "return false;", проверить нормально ли будут работать остальные проекты после этого.
        }

        $this->_logger->debug("'".$this->_lockName."' is locking by the file '".$this->_currentFilename."'");

        @touch($this->_currentFilename);
        @chmod($this->_currentFilename, 0664);

        $fres = @fopen($this->_currentFilename, "w");

        if (!$fres)
        {
            $this->_logger->fatal("Failed to open and lock '".$this->_currentFilename."'");
            throw new Exception("Failed to open and lock '".$this->_currentFilename."'");
        }

        $PID = posix_getpid();
        /** get process path */
        $processFile = @fopen('/proc/'.$PID.'/cmdline', 'r');

        $processPath = 'not defined';

        if ($processFile)
        {
            $processPath = fgets($processFile, 4096);
            fclose($processFile);
        }

        $this->_logger->info('New process: ['.$PID.'], path: ['.$processPath.']');

        fwrite($fres, $PID.'@@@'.time().'@@@'.$processPath);
        fclose($fres);

        return true;
    }

    public function isStopped()
    {
        return is_file($this->_currentFilename.'.stop');
    }

    public function isRestarted()
    {
        return is_file($this->_currentFilename.'.restart');
    }

    public function isExpired()
    {
        return time() > $this->_stopTime;
    }

    protected function storeCurrentReleaseAndInstall()
    {
        // GIRAR_BASE_DIR contains release version, constant created on worker start
        if(defined('GIRAR_BASE_DIR')) {
            // Get project name
            $releasePointsDir = basename(GIRAR_BASE_DIR);
            $nameParts = explode('-',$releasePointsDir);
            $releaseVersion = array_pop($nameParts);
            $projectName = implode('-',$nameParts);

            $projectPointsDir = '/home/projects/points/';

            if (is_link($projectPointsDir.$projectName)) {
                // store symlink to GIRAR_BASE_DIR
                $this->_linkToGirarBaseDir = $projectPointsDir.$projectName;

                $this->_girarBaseDirContentMarker = $this->getCurrentGirarBaseDirContentMarker();
            } else {
                $this->_logger->warn("Cant find link to GIRAR_BASE_DIR for project '$projectName'' in '$projectPointsDir''");
            }
        } else {
            throw new Exception('"GIRAR_BASE_DIR" named constant mast be defined!');
        }
    }

    protected function isInstallDetected()
    {
        if ($this->_girarBaseDirContentMarker) {
            $currentGirarBaseDirContentMarker = $this->getCurrentGirarBaseDirContentMarker();
            if ($currentGirarBaseDirContentMarker != $this->_girarBaseDirContentMarker && is_null($this->_restartDate)) {
                $this->_restartDate = time() + rand(0, 301);
                $this->_logger->debug('Install detected!|restart at ' . date("Y-m-d H:i:s", $this->_restartDate));
            }
        }

        if ($this->_restartDate != null && time() > $this->_restartDate)
        {
            $this->_restartDate = null;
            return true;
        }

        return false;
    }

    protected function getCurrentGirarBaseDirContentMarker()
    {
        $girarBaseDirContent = array();
        exec('ls -ls '. GIRAR_BASE_DIR, $girarBaseDirContent);
        return md5(implode('',$girarBaseDirContent));
    }

    protected function isReleaseDetected()
    {
        if($this->_linkToGirarBaseDir ) {
            // GIRAR_BASE_DIR contains release version, constant created on worker start
            // if link to GIRAR_BASE_DIR changed, new release is detected!
            $currentGirarBaseDir = readlink($this->_linkToGirarBaseDir);
            if ($currentGirarBaseDir !== false) {
                if (GIRAR_BASE_DIR != $currentGirarBaseDir && is_null($this->_restartDate)) {
                    $this->_restartDate = time() + rand(0, 301);
                    $this->_logger->debug('Release detected!|restart at ' . date("Y-m-d H:i:s", $this->_restartDate));
                }
            } else {
                $this->_logger->warn("Cant resolve symlink '{$this->_linkToGirarBaseDir}' to GIRAR_BASE_DIR. readlink() result:", $currentGirarBaseDir);
            }

        }

        if ($this->_restartDate != null && time() > $this->_restartDate)
        {
            $this->_restartDate = null;
            return true;
        }
        return false;
    }

    public function isNeedToStopOrRestart()
    {
        if ($this->isRestarted())
        {
            unlink($this->_currentFilename.'.restart');
            return true;
        }

        if ($this->_memoryLimit && (memory_get_usage(true) > $this->_memoryLimit)) return true;

        return $this->isStopped() || $this->isExpired() || $this->isReleaseDetected() || $this->isInstallDetected();
    }

    public function getLockInfo()
    {
        return $this->_lockInfo;
    }

    public function heartbeat()
    {
        return touch($this->_currentFilename);
    }


    protected function memoryLimit()
    {
        $limit = ini_get('memory_limit');

        if (preg_match('/^(\d+)(.)$/', $limit, $matches))
        {
            if ($matches[2] == 'M')
                $limit = $matches[1] * 1024 * 1024;
            elseif ($matches[2] == 'K')
                $limit = $matches[1] * 1024;
        }

        $limit = intval($limit);

        return ($limit > 1048576 ? intval($limit * 0.9) : null);
    }
}
