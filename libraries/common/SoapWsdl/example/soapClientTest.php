<?php
require_once 'SoapDemoSoapClient.php';
$soapClient = new SoapDemoSoapClient();
echo $soapClient->SayHello('qwe');