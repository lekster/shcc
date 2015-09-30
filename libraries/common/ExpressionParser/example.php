<?php

require_once "pbr-lib-common/src/ExpressionParser/class.ExpressionParser.php";

$expression = "!4+3+{sd} + {aaa}";
//$rpn = new RPN();
//echo $rpn->calculate($expression);
$parser = new ExpressionParser();
try {
	var_dump($parser->calc($expression,
		array(
			'sd' => '3',
			//'aaa' =>1,
			)));
} catch (Exception $e) {
	var_dump($e->getMessage());
}