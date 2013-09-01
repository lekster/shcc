<?php

use Doctrine\Common\ClassLoader;
use Guzzle\Http\Client;    
use Guzzle\Service\Description\ServiceDescription;

include 'bootstrap.php';

chdir(GIRAR_BASE_DIR);
//require_once  'pbr-lib-common/src/Doctrine/Common/ClassLoader.php';
require_once  'pbr-lib-common/src/Guzzle/vendor/autoload.php';

$client = new Guzzle\Service\Client();
$description = ServiceDescription::factory(dirname(__FILE__) . '/fc.json');
$client->setDescription($description);
//die('asd');
$command = $client->getCommand('Add', array("service_name" => "Mc_total_1day","request_data" => "79175305124"));
$responseModel = $client->execute($command);

var_dump( $responseModel['status']);
var_dump( $responseModel['message']);
//var_dump($responseModel);
