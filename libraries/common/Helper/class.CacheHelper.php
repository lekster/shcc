<?php
/**
 * Created by JetBrains PhpStorm.
 * User: shamgunov
 * Date: 30.05.14
 * Time: 11:51
 */

/**
 * Class Immo_MobileCommerce_CacheHelper
 *
 * Позволяет хранить в себе различные данные, с указанием времени жизни.
 * Ключи должны быть уникальны в пределах одного объекта кэша.
 */
class Immo_MobileCommerce_CacheHelper
{
    protected $_data = array();

    /**
     * @param $key
     * @param $data сохранять можно все кроме NULL
     * @param int $expire время жизни, секунд
     * @return bool удалось ли сохранить
     */
    public function set ($key, $data, $expire = 0)
    {
        if ($data != NULL) {
            $this->_data[$key] = new Immo_MobileCommerce_CacheItem($data, $expire);
            return true;
        } else {
            return false;
        }
    }

    public function get ($key)
    {
        if (array_key_exists($key, $this->_data)) {
            $cacheItem = $this->_data[$key];
            if (!$cacheItem->isExpired()) {
                return $cacheItem->getValue();
            } else {
                unset($this->_data[$key]);
                return null;
            }
        } else {
            return null;
        }
    }

    public function delete ($key)
    {
        if (array_key_exists($key, $this->_data)) {
            unset($this->_data[$key]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Чистим весь кэш
     */
    public function flush ()
    {
        $this->_data = array();
    }
}

class Immo_MobileCommerce_CacheItem
{
    protected $_value = null;
    protected $_expireAt = null;

    public function __construct($value, $expire)
    {
        $this->_value = $value;
        $expire = intval($expire);
        if ($expire != 0) {
            $this->_expireAt = time() + $expire;
        }
    }

    public function isExpired()
    {
        if (isset($this->_expireAt) && time() >= $this->_expireAt) {
            return true;
        } else {
            return false;
        }
    }

    public function getValue()
    {
        return $this->_value;
    }
}