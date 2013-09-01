<?php

include '../bootstrap.php';
$workDir = GIRAR_BASE_DIR;chdir($workDir);
var_dump(`pwd`);
require_once 'pbr-lib-common/src/Analytics/Btp/class.AnalyticsBtpSender.php';

$sender = new PBR_Analytics_Btp_Sender('udp://vps8147.mtu.immo', 8888);
while(1)
{
$op = array('select', 'delete', 'insert');
$rnd = rand(100000, 5000000);
$r1 = rand(0, 1);
//$r1 = 0;
$sender->sendData('mysql', 'vps3434.mtu.immo', $op[$r1], $rnd);
usleep(200000);
var_dump($rnd);
}

$config = array(
    'Analytics' => array
    (
        'Btp' => array
        (
            'host' => '123',
            'port' => '123',
        ),
        
    ),
    
);
?>

