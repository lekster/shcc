<?php
/**
 * Go! OOP&AOP PHP framework
 *
 * @copyright     Copyright 2012, Lissachenko Alexander <lisachenko.it@gmail.com>
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
error_reporting(E_ALL); ini_set('display_errors', '1');

include '../htdocs/bootstrap.php';
chdir(GIRAR_BASE_DIR);

require_once __DIR__ . '/autoload_aspect.php';

use Doctrine\Common\Annotations\AnnotationReader;

$man = new Human();
echo "::::::Want to eat something, let's have a breakfast!", PHP_EOL;
$man->eat();
echo "I should work to earn some money", PHP_EOL;
$man->work();
echo "It was a nice day, go to bed", PHP_EOL;
$man->sleep();

echo "check cache" . PHP_EOL;
$man->cacheMe('123');
$man->cacheMe('123');
