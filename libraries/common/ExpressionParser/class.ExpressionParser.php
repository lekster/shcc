<?php


require_once "pbr-lib-common/src/ExpressionParser/class.RPN.php";
require_once "pbr-lib-common/src/ExpressionParser/VariableResolver/class.StandartVariableResolver.php";

class ExpressionParser
{
	protected $variablesArr = array();
	protected $variableResolver;

	public function __construct()
	{
		$this->variableResolver = new StandartVariableResolver();
	} 

	


	public function calc($expression, $vars = array())
	{
		$expressionNew = strtolower($expression);
		$vars = array_change_key_case($vars, CASE_LOWER); //ignore case, неясно надо это или нет, т.к. есть конфликты (также ниже str_ireplace и в методе getVariablesFromExpression)
		$this->variablesArr = array_merge($this->variablesArr, $vars);
		$this->variableResolver->putVars($this->variablesArr);
		$this->variableResolver->putExpression($expression);
		//парсим исходную строку и для каждой переменной вызываем VariableResolver

		$variablesList = $this->variableResolver->getVariablesFromExpression();
		$variables =  array();
		//var_dump($variablesList);var_dump($this->variableResolver);die();	

		foreach ($variablesList as $key)
		{
			$variables[$key] = $this->variableResolver->resolveVariable($key);
		}
		
		foreach ($variables as $key => $value)
		{
			$expressionNew = str_ireplace($key, $value, $expressionNew);
		}

		$rpn = new RPN();
		try 
		{
			$ret = $rpn->calculate($expressionNew);
			//var_dump("COND|$expression|$expressionNew|$ret");die();
			return $ret;
		} 
		catch (Exception $e)
		{
			throw new Exception("Exception while calc|" . $expression . "|" . $expressionNew . "|" . $e->getMessage());
		}
		
	}

	public function setVariableResolver($reslover)
	{
		$this->variableResolver = $reslover;
	}

	public function putVariable($key, $val)
	{
		$this->variablesArr[$key] = $val;
	}

	public function clearVariables()
	{
		$this->variablesArr = array();
	}
}

