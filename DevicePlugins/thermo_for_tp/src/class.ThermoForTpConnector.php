<?php

class ThermoForTpConnector
{
    protected $ip;
    protected $port;

    public function __construct($ipAddr = '10.192.163.44', $port = 12345)
    {
        $this->ip = $ipAddr;
        $this->port = $port;
    }

    protected function crc(array $dataBuf)
    {
        $CRC8INIT  =  0x00;
        $CRC8POLY = 0x18;   

        $crc = $CRC8INIT;
        $len = count($dataBuf);
        for ($loop_count = 0; $loop_count != $len; $loop_count++)
        {
            $b = $dataBuf[$loop_count];
            
            $bit_counter = 8;
            do {
                $feedback_bit = ($crc ^ $b) & 0x01;

                if ( $feedback_bit == 0x01 ) {
                    $crc = $crc ^ $CRC8POLY;
                }
                $crc = ($crc >> 1) & 0x7F;
                if ( $feedback_bit == 0x01 ) {
                    $crc = $crc | 0x80;
                }
            
                $b = $b >> 1;
                $bit_counter--;
            
            } while ($bit_counter > 0);
        }
            
        return $crc;

    }
    
    protected function SendCommand($cmd, $paramsArr)
    {
        $data = array_merge(array($cmd), $paramsArr);
        /*
        $data[0] = 0x31;
        $data[1] = 0x32;
        $data[2] = 0xAF;
        $data[3] = 0xC4;
        */
        $msg[0] = $this->crc($data);
        $msg = array_merge($msg, $data);
        //var_dump($msg);

        //die('asd');

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($sock ,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));
        $result = socket_connect($sock, $this->ip, $this->port);
        $msg = implode(array_map("chr",$msg));
        $len = strlen($msg);
        //var_dump($msg);
        socket_send($sock, $msg, $len, 0);
        $recvLen = @socket_recv($sock, $buf, 12, 0);
        socket_close($sock);
        if ($recvLen == 0)
        {
            return null;
        }
        
        //var_dump($buf);
        
        //сравниваем crc    
        /*$getCrc = array_shift($buf);
        //формируем массив данными возврата, если их нет то NULL
        if ($this->crc($buf) != $getCrc)
        {
            return null;
        }
        array_shift($buf);
        */
        $string = "";
        for ($i = 2; $i < $recvLen;$i++ )
        {
            $string .= bin2hex($buf[$i]);
        }
        //var_dump($buf);
        //var_dump($string);
        return $string;
    }
    
    //return int
    public function GetNeedTemp()
    {
        $cmd = 0x01;
        $ret = $this->SendCommand($cmd, array());
        if (!is_null($ret))
        {
            $ret = base_convert($ret, 16,10);
            if ($ret > 128) $ret = $ret - 256;
        }
        return $ret;
    }    
    
    //return int
    public function GetRealTemp()
    {
        $cmd = 0x02;
        $ret = $this->SendCommand($cmd, array());
        if (!is_null($ret))
        {
            $ret = base_convert($ret, 16,10);
            if ($ret > 128) $ret = $ret - 256;
        }
        return $ret;
    }   

    //return int
    public function GetRealAirTemp()
    {
        $cmd = 0x03;
        $ret = $this->SendCommand($cmd, array());
        if (!is_null($ret))
        {
            $ret = base_convert($ret, 16,10);
            if ($ret > 128) $ret = $ret - 256;
        }
        return $ret;
    }       
    
    //int
    //return int
    public function SetNeedTemp($val)
    {
        if (!is_numeric($val) || $val >= 128 || $val <= -127)
            return null;
        if ($val < 0) $val = 256 + $val;    
        $cmd = 0x04;
        $ret = $this->SendCommand($cmd, array($val));
        if (!is_null($ret))
        {
            $ret = base_convert($ret, 16,10);
            if ($ret > 128) $ret = $ret - 256;
        }
        return $ret;
    }
    
    //return int
    public function GetWorkState()
    {
        $cmd = 0x05;
        $ret = $this->SendCommand($cmd, array());
        if (!is_null($ret))
        {
            $ret = base_convert($ret, 16,10);
        }
        return $ret;
    }   
    
    ///return string
    public function GetIp()
    {
        $cmd = 0x06;
        $ret = $this->SendCommand($cmd, array());
        if (!preg_match('/([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/i', $ret, $matches))
            return null;
        array_shift($matches);
        for($i = 0;$i < 4; $i++)
            $matches[$i] = base_convert($matches[$i], 16,10);
        $ret = implode($matches, '.');
        return $ret;
    }
    
    //string, check by regexp ipv4
    //return string
    public function SetIp($val = '192.168.1.31')
    {
        $cmd = 0x07;
        if (!preg_match('/([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)/', $val, $matches))
            return null;
        ///var_dump($matches);die('asd');
        $ip = array($matches[1], $matches[2], $matches[3], $matches[4]);
        $ret = $this->SendCommand($cmd, $ip);
        //$ret = "c0a8011f";
        //var_dump($ret);
        if (!preg_match('/([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/i', $ret, $matches))
            return null;
        array_shift($matches);
        for($i = 0;$i < 4; $i++)
            $matches[$i] = base_convert($matches[$i], 16,10);
        $ret = implode($matches, '.');
        //var_dump($ret);        
        return $ret;
       
    }       
    
    
    //ret string
    public function GetMac()
    {
        $cmd = 0x08;
        $ret = $this->SendCommand($cmd, array());
        return $ret;
    }
    
    //string, check regexp 6byte hex
    //ret string
    //ret string
    public function SetMac($val)
    {
        $cmd = 0x09;
        if (!preg_match('/([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/i', $val, $matches))
            return null;
        ///var_dump($matches);die('asd');
        $mac = array($matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]);
        $ret = $this->SendCommand($cmd, $mac);
        //$ret = "c0a8011fa1b1";
        if (!preg_match('/([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})([a-f0-9]{2})/i', $ret, $matches))
            return null;
        //var_dump($ret);        
        return $ret;
    }

    public function GetMaxReleWorkTimeCount()
    {
        $cmd = 0x12;
        $ret = $this->SendCommand($cmd, array());
        if (!preg_match('/([a-f0-9]{2})([a-f0-9]{2})/i', $ret, $matches))
            return null;
        $ret = base_convert($matches[1], 16,10) * 256 + base_convert($matches[2], 16,10);
        return $ret;

    }
    
    public function SetMaxReleWorkTimeCount($val)
    {
        if (!is_numeric($val) || $val >= 65535 || $val < 0)
            return null;
        $cmd = 0x13;
        
        $ret = $this->SendCommand($cmd, array((int)($val / 256), $val % 256));
        return $ret;

    }

    public function GetTempGisteresis()
    {
        $cmd = 0x10;
        $ret = $this->SendCommand($cmd, array());
        if (!is_null($ret))
        {
            $ret = base_convert($ret, 16,10);
        }
        return $ret;

    }
    
    public function SetTempGisteresis($val)
    {
        $cmd = 0x11;
        if (!is_numeric($val) || $val >= 255 || $val < 0)
            return null;
        $ret = $this->SendCommand($cmd, array($val));
        if (!is_null($ret))
        {
            $ret = base_convert($ret, 16,10);
        }
        return $ret;
    }



}

