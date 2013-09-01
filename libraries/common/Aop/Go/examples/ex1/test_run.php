<?php

$time = microtime(1);

for ($i = 0; $i < 100; $i++)
{
	exec('php -f ex1.php');	
}

$time = microtime(1) - $time;
echo $time;