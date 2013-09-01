<?php

require_once ("libraries/common/PhpConf/Config.php");

define('GIRAR_BASE_DIR', './');

/**
 * Объект для чтения PHP конфигов.
 * Объект позволяет получать значения из PHP конфигов, а также делать автозамены в возвращаемых значениях.
 * 
 * Пример:
 * <code>
 * try {
 *  $reader = new PhpConf_Reader('default.conf');
 * } catch (PhpConf_Exception $e) {
 *  die($e->getMessage());
 * }
 * $a = $reader->get('option', 'operator_id');
 * $b = $reader->get('message', 'help', 'nick', $nick);
 * $c = $reader->get('message', 'hello', array('nick'), array($nick));
 * </code>
 * 
 * <b>Итеративный доступ.</b><br>
 * Предназначен для последовательной работы со значениями ключей секции. С ключами такой способ доступа не работает, т. е. итеративные методы возвращают сразу значение ключа.<br>
 * Пример:
 * <code>
 * try {
 *  $reader = new PhpConf_Reader('default.conf');
 * } catch (PhpConf_Exception $e) {
 *  die($e->getMessage());
 * }
 * $reader->select('users');
 * while ( ($user = $reader->next()) != NULL ) {
 *  $users[] = $user;
 * }
 * 
 * $reader->select('msg');
 * $msg = $reader->next('%pin%', '1234');
 * </code>
 * 
 * <b>31/01/2007 [1.0.1]</b>
 * Добавлены методы для итеративного доступа к данным: <b>select</b>, <b>next</b>.
 * 
 * @version 1.1.0
 * @copyright Copyright Inform-mobil 2007
 * @author vadim
 */
class PhpConf_Reader extends PhpConf_Config
{
    private $iterator = array(false, false);
    private $iteratorSection = false;
    private $iteratorIndex = 0;
    private $iteratorSize = 0;
    
    public function __construct($filename)
    {
        parent::__construct($filename);
    }
        
    /**
     * Возвращает параметр из конфига.
     * Параметр выбирается по названию секции и ключа. Метод также может делать автоматические замены в возвращаемых значениях с помощью параметров search и replace. Эти параметры могут быть как строками, так и массивами. Если параметр в конфиге не найден, возвращается NULL. 
     * 
     * @param string $key название ключа
     * @param string $section название секции
     * @param mixed $search параметр поиска
     * @param mixed $replace параметр замен
     * @return mixed
     */
    public function get($key, $section = false, $search = false, $replace = false)
    {
        if (empty($section)) {
            if (empty($this->iteratorSection)) {
                return NULL;
            }
            
            $section = $this->iteratorSection;
        }
        
        if (isset($this->data[$section])) {
            if (isset($this->data[$section][$key])) {
                $data = $this->data[$section][$key];
                if ($search && $replace) {
                    return str_replace($search, $replace, $data);
                } else {
                    return $data;
                }
            }
        }
        
        return NULL;
    }
    
    /**
     * Выбирает секцию для итераций.
     * Обнуляет счетчик итераций. Если указанной секции не существует, ничего не происходит.
     * 
     * @param string $section имя секции для итераций
     * @return void
     */
    public function useSection($section)
    {
        if (isset($this->data, $section)) {
            $this->iteratorSection = $section;
            $this->iteratorIndex = 0;
            $this->iteratorSize = count($this->data[$section]);
        }
    }
    
    /**
     * Итератор по значениям секции.
     * Для итеративного доступа, необходимо сначала выбрать секцию с помощью метода select. Метод возвращает следующеее значение секции, начиная с первого. При достижении конца секции, возвращается NULL. Также NULL возвращается, если секция не выбрана.
     * 
     * @param mixed $search параметр поиска
     * @param mixed $replace параметр замен
     */
    /*
    public function next($search = false, $replace = false)
    {
        if ($this->iteratorSection && $this->iteratorIndex < $this->iteratorSize) {
            return $this->get($this->iteratorSection, $this->iteratorIndex++, $search, $replace);
        }
        
        return NULL;
    }*/
    
    /**
     * Возвращает секцию из конфига.
     * Если секция не найдена, возвращается NULL.
     * 
     * @param string $section имя секции
     */
    public function getSection($section)
    {
        if (isset($this->data[$section])) {
            return $this->data[$section];
        }
        
        return NULL;
    }
}

?>