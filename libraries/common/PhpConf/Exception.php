<?php

/**
 * Объект исключения PHP конфигов.
 * Для идентификации исключений используйте коды, которые содержатся в константных полях абстрактного объекта PhpConf_Config.
 * 
 * <code>
 * ...
 * catch (PhpConf_Exception $e) {
 *  switch($e->getCode()) {
 *      case PhpConf_Config::EXC_OPENFAIL:
 *          break;
 *  }
 * }
 * </code>
 * 
 * @author vadim
 */
class PhpConf_Exception extends Exception {}

?>