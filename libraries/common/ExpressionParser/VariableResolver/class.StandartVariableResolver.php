<?php

require_once "pbr-lib-common/src/ExpressionParser/VariableResolver/abstract.VariableResolver.php";

class StandartVariableResolver extends VariableResolver
{
	public function resolveVariable($variableName)
	{
		
		if (!isset($this->variablesArr[$variableName]))
			throw new Exception("Variable not define|$variableName");
		return $this->variablesArr[$variableName];
	}


}