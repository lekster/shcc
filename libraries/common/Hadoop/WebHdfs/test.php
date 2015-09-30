<?php
require_once 'pbr-lib-common/src/Hadoop/WebHdfs/WebHDFS.php';


$hdfs = new WebHDFS('vps2236.mtu.immo', '50070', 'hadoop');
$hdfs->mkdirs('/1234');

$hdfs->create('/1234/mc_stat_2014-01-09.csv', '/tmp/mc_stat_2014-01-09.csv');
$hdfs->create('/1234/mc_stat_2014-01-09.csv.bz2', '/home/asmirnov/workspace/pbr-lib-common/src/Hadoop/mc_stat_2014-01-09.csv.bz2');


//$hdfs->delete('/1234/cuba.7z');
//$hdfs->delete("/1234/1.log" );
//$hdfs->create("/1234/1.log", "/tmp/f.out" );

//$text = date("Y-m-d H:i:s") . ":__test" . PHP_EOL;
//exec("echo " . '"' . $text . '"' . " > /tmp/f.out");
//$hdfs->append("/1234/1.log", $text );

/*$response = $hdfs->open('/1234/1.log');
echo $response;

$hdfs->downloadFile('/1234/1.log', '/tmp/2.out');
$hdfs->downloadFile('/1234/cuba.7z', '/home/asmirnov/cuba_22.7z');
*/

exit;
       