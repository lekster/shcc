<?php

require_once dirname(__FILE__) . "/src/class.ThermoForTpConnector.php";


$package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
        $socket  = @socket_create(AF_INET, SOCK_RAW, 1);
        @socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => 1, 'usec' => 0));
        @socket_connect($socket, "192.168.1.131", null);
        $ts = microtime(true);
        @socket_send($socket, $package, strLen($package), 0);
        if (@socket_read($socket, 255)) {
            $result = microtime(true) - $ts;
        } else {
            $result = false;
        }
        @socket_close($socket);
var_dump($result);
exec("ping -c2 192.168.1.31", $out);
var_dump($out);

/*

leks@coldfire:/home/projects/php/projects/devel/majordomo$ ping -c2  192.168.1.31
PING 192.168.1.31 (192.168.1.31) 56(84) bytes of data.
64 bytes from 192.168.1.31: icmp_seq=1 ttl=64 time=2.33 ms
64 bytes from 192.168.1.31: icmp_seq=2 ttl=64 time=1.49 ms

/[0-9]+ bytes from $host:/i

*/

die(' 123');

$a = new ThermoForTpConnector();
$a->GetNeedTemp();
//var_dump($a->SetNeedTemp(-2));
//$a->SetIp();
//var_dump($a->SetMac("010203040506"));

function crc(array $dataBuf)
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


//$k = 0xFF;
//var_dump($k);
//var_dump(crc(array(0x00,0x01,0x03)));
//var_dump(bin2hex($k));


$data[0] = 0x31;
$data[1] = 0x32;
$data[2] = 0xAF;
$data[3] = 0xC4;
$msg[0] = crc($data);
$msg = array_merge($msg, $data);
var_dump($msg);

//die('asd');

$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
socket_set_option($sock ,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>1, "usec"=>0));
$srvIP = '10.192.163.44';
$srvPort = 12345;
$result = socket_connect($sock, $srvIP, $srvPort);

$msg = implode(array_map("chr",$msg));
$len = strlen($msg);
var_dump($msg);
socket_send($sock, $msg, $len, 0);
$recvLen = socket_recv($sock, $buf, 12, 0);

$string = "";
for ($i = 0; $i < $recvLen;$i++ )
{
    $string .= bin2hex($buf[$i]);
}
var_dump($buf);
var_dump($string);
socket_close($sock);
