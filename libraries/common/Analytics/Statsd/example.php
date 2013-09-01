<?php

include '../../../bootstrap.php';
$workDir = GIRAR_BASE_DIR;chdir($workDir);
var_dump(`pwd`);
require_once 'pbr-lib-common/src/Analytics/Statsd/class.AnalyticsStatsdSender.php';


while(1)
{

$a = new PBR_Analytics_Statsd_Sender('vps8160.mtu.immo', '8125');
$a->gauge('test_123', rand(100,150));
$a->timing('test_123_run_time', rand(50,150));

usleep(300000);
}
?>
