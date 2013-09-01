<?php

require('GELFMessage.php');
require('GELFMessagePublisher.php');

$message = new GELFMessage();
$message->setShortMessage('something is broken.');
$message->setFullMessage("lol full message!");
$message->setHost('vps7148.mtu.immo');
$message->setLevel(GELFMessage::CRITICAL);
$message->setFile('/var/www/example.php');
$message->setLine(1337);
$message->setAdditional("something", "foo");
$message->setAdditional("something_else", "bar");

$publisher = new GELFMessagePublisher('vps8242.mtu.immo');
$publisher->publish($message);