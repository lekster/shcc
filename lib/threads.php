<?php

// Thanks to http://habrahabr.ru/post/75454/

class Threads {
    public $phpPath = 'php';
    
    private $lastId = 0;
    private $descriptorSpec = array(
        0 => array('pipe', 'r'),
        1 => array('pipe', 'w')
    );
    private $handles = array();
    private $streams = array();
    private $results = array();
    private $pipes = array();
    private $commandLines = array();
    private $timeout = 10;
    private $lastCheck = 0;
    
    public function newThread($filename, $params=array()) {
        if (!file_exists($filename)) {
            throw new ThreadsException('FILE_NOT_FOUND');
        }
        
        $params = addcslashes(serialize($params), '"');
        $command = $this->phpPath.' -q '.$filename.' --params "'.$params.'"';
        ++$this->lastId;

        $this->commandLines[$this->lastId] = $command;        
        $this->handles[$this->lastId] = proc_open($command, $this->descriptorSpec, $pipes);
        $this->streams[$this->lastId] = $pipes[1];
        $this->pipes[$this->lastId] = $pipes;
        
        return $this->lastId;
    }

    public function newXThread($filename, $display='101', $params=array()) {
        /*
        Функция создает поток на отдельном экране в LINUX.
        */
        if (!(substr(php_uname(), 0, 5) == "Linux")) {
            throw new ThreadsException('FOR_LINUX_ONLY');
        }
        if (!file_exists($filename)) {
            throw new ThreadsException('FILE_NOT_FOUND');
        }
        
        $params = addcslashes(serialize($params), '"');
        $command = 'DISPLAY=:'.$display.' '.$this->phpPath.' '.$filename.' --params "'.$params.'"';
        ++$this->lastId;

        $this->commandLines[$this->lastId] = $command;        
        $this->handles[$this->lastId] = proc_open($command, $this->descriptorSpec, $pipes);
        $this->streams[$this->lastId] = $pipes[1];
        $this->pipes[$this->lastId] = $pipes;
        
        return $this->lastId;
    }

    public function getPipes() {
     return $this->pipes;
    }
    
    public function iteration() {
        if (!count($this->streams)) {
            return false;
        }
        $read = $this->streams;

        if (false === ($number_of_streams=stream_select($read, $write=null, $except=null, $this->timeout))) {
         DebMes("No active streams");
         return 0;
        }

       /* 
        $stream = next($read);
        if (!$stream) {
         reset($read);
         $stream=current($read);
        }
        */

        foreach($read as $stream) 
        {

            $id = array_search($stream, $this->streams);
            $result = stream_get_contents($this->pipes[$id][1]);
            if (feof($stream) || ($contents = fread($stream, 255))==false) 
            {
                echo "\n".date('H:i:s')." Closing thread: ".$this->commandLines[$id];
                DebMes("Closing thread: ".$this->commandLines[$id]);
                fclose($this->pipes[$id][0]);
                fclose($this->pipes[$id][1]);
                proc_close($this->handles[$id]);
                unset($this->handles[$id]);
                unset($this->streams[$id]);
                unset($this->pipes[$id]);
                unset($this->commandLines[$id]);
            } 
            else 
            {
              echo $contents;
              //echo "\n".date('H:i:s')." Thread is running OK: ".$this->commandLines[$id];
            }

        }

        return $result;
    }
    
    public static function getParams() {
        foreach ($_SERVER['argv'] as $key => $argv) {
            if ($argv == '--params' && isset($_SERVER['argv'][$key + 1])) {
                return unserialize($_SERVER['argv'][$key + 1]);
            }
        }
        return false;
    }
    
}

class ThreadsException extends Exception {
}







/*
    <?php

    class Threads {
        public $phpPath = 'php';
        
        private $lastId = 0;
        private $descriptorSpec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w')
        );
        private $handles = array();
        private $streams = array();
        private $results = array();
        private $pipes = array();
        private $timeout = 10;
        
        public function newThread($filename, $params=array()) {
            if (!file_exists($filename)) {
                throw new ThreadsException('FILE_NOT_FOUND');
            }
            
            $params = addcslashes(serialize($params), '"');
            $command = $this->phpPath.' -q '.$filename.' --params "'.$params.'"';
            ++$this->lastId;
            
            $this->handles[$this->lastId] = proc_open($command, $this->descriptorSpec, $pipes);
            $this->streams[$this->lastId] = $pipes[1];
            $this->pipes[$this->lastId] = $pipes;
            
            return $this->lastId;
        }
        
        public function iteration() {
            if (!count($this->streams)) {
                return false;
            }
            $read = $this->streams;
            stream_select($read, $write=null, $except=null, $this->timeout);
             
          //      Здесь береться только один поток для удобства обработки 
          //      на самом деле в массиве $read их зачастую несколько
            
            $stream = current($read);
            $id = array_search($stream, $this->streams);
            $result = stream_get_contents($this->pipes[$id][1]);
            if (feof($stream)) {
                fclose($this->pipes[$id][0]);
                fclose($this->pipes[$id][1]);
                proc_close($this->handles[$id]);
                unset($this->handles[$id]);
                unset($this->streams[$id]);
                unset($this->pipes[$id]);
            }
            return $result;
        }
        
        
        //    Статичный метод для получения параметров из 
        //    параметров командной строки
        
        public static function getParams() {
            foreach ($_SERVER['argv'] as $key => $argv) {
                if ($argv == '--params' && isset($_SERVER['argv'][$key + 1])) {
                    return unserialize($_SERVER['argv'][$key + 1]);
                }
            }
            return false;
        }
        
    }

    class ThreadsException extends Exception {
    }

    ?>



Теперь для примера создадим test.php:

    <?php
    $start = microtime(true);

    require './threads.php';

    $threads = new Threads;

    for ($i=0;$i<10;$i++) {
        $threads->newThread('./delay.php', array('delay' => rand(1, 5)));
    }

    while (false !== ($result = $threads->iteration())) {
        if (!empty($result)) {
            echo $result."\r\n";
        }
    }

    $end = microtime(true);
    echo "Execution time ".round($end - $start, 2)."\r\n";

    ?>



И delay.php который он вызывает:

    <?php

    require './threads.php';

    if ($params = Threads::getParams()) {
        sleep($params['delay']);
        echo 'Wait for '.$params['delay'].' s.';
    }

    ?>



В результате выполнения test.php получаем следующее:

    Microsoft Windows [Version 6.1.7100]
    Copyright (c) 2009 Microsoft Corporation. All rights reserved.

    Z:\home\labs\www\threads>php test.php
    Wait for 1 s.
    Wait for 5 s.
    Wait for 4 s.
    Wait for 5 s.
    Wait for 5 s.
    Wait for 5 s.
    Wait for 3 s.
    Wait for 3 s.
    Wait for 2 s.
    Wait for 4 s.
    Execution time 5.58

    Z:\home\labs\www\threads>

*/


?>