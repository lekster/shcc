<?php

/**
 * @see https://github.com/barbushin/multirequest
 * @author Barbushin Sergey http://linkedin.com/in/barbushin
 *
 */
class MultiRequest_Callbacks {

	protected $callbacks;

	public function add($name, $callback, $params, $object) {
		if($object)
			$callable = !is_callable(array($object,$callback));
		else
			$callable = true;
		if(!is_callable($callback) && $callable) {
			if(is_array($callback)) {
				$callbackName = (is_object($callback[0]) ? get_class($callback[0]) : $callback[0]) . '::' . $callback[1];
			}
			else {
				$callbackName = $callback;
			}
			throw new Exception('Callback "' . $callbackName . '" with name "' . $name . '" is not callable');
		}
		$this->callbacks[$name][] = array('funcName' => $callback, 'funcAddParams' => $params, 'object' => $object);
	}

	public function call($name, $arguments) {
		if(isset($this->callbacks[$name])) {
			foreach($this->callbacks[$name] as $callback) {
				$arguments = array_merge($arguments,array($callback['funcAddParams']));
				if($callback['object'])
					call_user_func_array(array($callback['object'],$callback['funcName']), $arguments);
				else
					call_user_func_array($callback['funcName'], $arguments);
			}
		}
	}

	public function __call($method, $arguments = array()) {
		$this->call($method, $arguments);
	}
}