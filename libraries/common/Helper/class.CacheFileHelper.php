<?php
/**
 * Class Immo_MobileCommerce_CacheFileHelper
 *
 * Позволяет кэшировать данные в файл с указанием времени жизни кэша в секундах. По-умолчанию 5 минут.
 */

class Immo_MobileCommerce_CacheFileHelper
{
    protected $cacheDir;
    protected $mainExpire = 300;

    public function __construct($cacheDir, $mainExpire = null)
    {
        $this->cacheDir = $cacheDir;
        if(!is_null($mainExpire))
            $this->mainExpire = $mainExpire;
    }

    /**
     * @param $key - ключ - название файла
     * @param $data - данные для кэширования
     * @param int $expire - время жизни в секундах
     *
     * @return boolean - удалось ли записать в файл
     */
    public function set($key, $data, $expire = null)
    {
        if(is_null($expire))
            $expire = $this->mainExpire;
        $fileName = $this->cacheDir.'/'.$key;
        $data = serialize($data);

        $resData = array(
            'data' => $data,
            'expire' => $expire,
        );

        $cacheData = json_encode($resData);
        try
        {
            if(file_put_contents($fileName, $cacheData))
                return true;
            else
                return null;
        }
        catch (Exception $e)
        {
            var_dump($e->getMessage());
        }
    }

    /**
     * Возвращает закэшированные данные
     *
     * @param $key - ключ - название файла
     * @param bool $isReturnExpired - если true возвращает данные даже если кэш просрочен
     * @return mixed|null - возвращает закэшированные данные, либо null если не удалось
     */
    public function get($key, $isReturnExpired = false)
    {
        $fileName = $this->cacheDir.'/'.$key;
        if(is_readable($fileName))
        {
            $cache = json_decode(file_get_contents($fileName), true);
            if($cache && isset($cache['data']) && $cache['expire'])
            {
                $cacheItem = new Immo_MobileCommerce_CacheFileItem($cache['data'], $cache['expire'], $fileName);
                $value = $cacheItem->getData();

                if($isReturnExpired)
                {
                    return $value;
                }

                $isExpired = $cacheItem->isExpired();
                if(!$isExpired && $value)
                {
                    return $value;
                }
                else
                {
                    return null;
                }
            }
            else
            {
                return null;
            }
        }
        else
            return null;
    }

    /**
     * Удаляет файл с кэшем по ключу
     * @param $key
     * @return bool
     */
    public function delete($key)
    {
        if(file_exists($this->cacheDir.'/'.$key))
        {
            $isDelete = unlink($this->cacheDir.'/'.$key);
            return $isDelete;
        }
        else
            return false;
    }

    /**
     * Возвращает массив с названиями файлов с датой модификации меньше текущей
     * @param $limit - кол-во файлов
     * @param int $minutesWhithoutModify - кол-во минут с последней модификации
     * @return array
     */
    public function findByModify($limit, $minutesWhithoutModify=0)
    {
        // Если filename не каталог возвращаем false
        if(!is_dir($this->cacheDir))
            return false;

        $keys = array();
        $files = array();
        exec('find '.$this->cacheDir. ' -maxdepth 1 -mmin +'.$minutesWhithoutModify.' -type f', $files);
        $n = 0;
        foreach ($files as $filename)
        {
            if (++$n > $limit) break;
            $keys[] = basename($filename);
        }

        return $keys;
    }
}


class Immo_MobileCommerce_CacheFileItem
{
    protected $data = null;
    protected $expire;
    protected $fileName;

    public function __construct($data, $expire, $fileName)
    {
        if($data)
            $this->data = $data;

        $this->expire = $expire;
        $this->fileName = $fileName;
    }

    public function getData()
    {
        if(!is_null($this->data))
            return unserialize($this->data);
        else
            return null;
    }
    public function isExpired()
    {
        $expireMin = round(intval($this->expire) / 60);
        exec('find '.$this->fileName.' -maxdepth 1 -mmin +'.$expireMin.' -type f', $file);
        if(count($file) > 0)
            return true;
        return false;
    }
}