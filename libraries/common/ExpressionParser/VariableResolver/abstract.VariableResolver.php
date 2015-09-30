<?php

abstract class VariableResolver
{
	protected $variablesArr;
	protected $expression;

	public function __construct()
	{
		
	}
	
	public function putVars(array $variables)
	{
		foreach ($variables as $key => $value)
		{
			$this->variablesArr["#{" . $key . "}"] = $value;
		}
	}

	public function putExpression($expression)
	{
		$this->expression = $expression;
	}

	public function getVariablesFromExpression()
	{
		$ret = array();
		$expr = "/\#\{[a-z_]{1}[a-z0-9_]+\}/i";
		preg_match_all($expr, $this->expression, $matches);
		if (is_array($matches[0]))
		{
			foreach ($matches[0] as $value)
			{
				$ret[] = strtolower($value);
			}
		}
		return $ret;
	}


	public abstract function resolveVariable($variableName);

}