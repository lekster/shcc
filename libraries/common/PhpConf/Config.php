<?php



/**
 * Абстрактный PHP конфигуратор.
 * Конфигуратор использует PHP файлы в качестве конфигов. Для разбора подключаемых конфигов используется быстрый нативный PHP парсер. Структура конфига - это PHP код возвращающий двумерный массив, где строки - это названия секций, а столбцы - названия ключей.
 * 
 * Пример:
 * <code> 
 * <?php
 * return array(
 *   'section' => array(
 *     'key' => 'value'
 *   )
 * );
 * ?>
 * </code>
 * 
 * <b>Расширения PHP конфигов.</b><br>
 * Конфиги можно расширять, используя секцию <b>extends</b>. Эта секция должна указывать на строку: относительный путь к расширяемому конфигу (относительно расширяющего) или абсолютный путь к конфигу-родителю. Совпадающие ключи конфига-наследника заменяют ключи конфига-родителя. Уровней расширения может быть бесконечное множество. Секция <b>extends</b>, также, поддерживае множественное наследование, в этом случае она должна содержать массив.
 * 
 * Пример:
 * <code>
 * <?php
 * return array(
 *   array(
 *     'extends' => 'default.conf'
 *   )
 * );
 * ?>
 * </code>
 *
 * <b>Чтение PHP конфигов.</b><br>
 * Для чтения PHP конфигов используйте класс PhpConf_Reader.
 * 
 * <b>Изменение/создание PHP конфигов.</b><br>
 * Для программного изменения/создания PHP конфигов используйте класс PhpConf_Writer. 
 * 
 *  
 * <b>17/01/2007 [1.0.0]</b>
 * 1. Первый релиз.
 * 
 * <b>17/01/2007 [1.0.1]</b>
 * 1. Добавлена строка с предупреждением в записываемый конфиг.
 * 2. Добавлена возможность удаления всей секции + тесты.
 * 
 * <b>16/05/2007 [1.1.0]</b>
 * 1. Добавлена возможность множественного наследования.
 * 2. Портированы тесты под rcs tg/tr
 * 
 * @version 1.1.0
 * @copyright Copyright Inform-mobil 2007
 * @author vadim
 */
abstract class PhpConf_Config
{
    const EXC_OPENFAIL = 0;
    const EXC_BADFORMAT = 1;
    const EXC_WRITEFAIL = 2;
    
    protected $filename;
    protected $data = array();
    
    private $parseHistory = array();
    
    /**
     * Конструктор.
     * Производит разбор PHP конфига и сохраняет данные.
     * 
     * @param string $filename имя файла
     * @param boolean $is_extending флаг использования расширений конфигов
     * @throws PhpConf_Exception если файл не найден или имеет неверный формат
     */
    protected function __construct($filename, $is_extending = true)
    {
        $this->filename = $filename;
        $this->data = $this->parse($filename, $is_extending);
    }
    
    /**
     * Разбирает PHP конфиг.
     * 
     * @param string $filename имя файла
     */
    protected function parse($filename, $is_extending)
    {
        $realPath = realpath(GIRAR_BASE_DIR . "/" . $filename);
    	//var_dump($filename);
        //var_dump($realPath);
        //var_dump(realpath($filename));
        //var_dump(file_exists(GIRAR_BASE_DIR . "/" . $filename));
    	//var_dump(GIRAR_BASE_DIR . "/" . $filename);
        if (in_array($realPath, $this->parseHistory)) {
            return array();
    	}
    	
    	$this->parseHistory[] = $realPath;
    	
        if (!$fp = @fopen($filename, 'rb', true)) throw new PhpConf_Exception('could not open file: "'.$filename.'"', PhpConf_Config::EXC_OPENFAIL);
        fclose($fp);
        
        $data = include($filename);
        if (!is_array($data)) throw new PhpConf_Exception('wrong config file format', PhpConf_Config::EXC_BADFORMAT);
        if (isset($data['extends']) && $is_extending) {
            //die("asd");
            //var_dump($data['extends']);die();
            if (!is_array($data['extends'])) {
                if ($data['extends'][0] != '/' && !@fopen($data['extends'], 'rb', true))
                	$data['extends'] = dirname($filename).'/'.$data['extends'];
                //var_dump($data['extends']);die();
                $super = $this->parse($data['extends'], true);
                //var_dump($super);die();
                unset($data['extends']);
                foreach ($super as $section => $value) {
                		$section = (string) $section;
                    $data[$section] = isset($data[$section]) ? $this->array_merge($value, $data[$section]) : $value;
                }
            } else {
                foreach ($data['extends'] as $extends) {
                    if ($extends[0] != '/' && !@fopen($extends, 'rb', true))
                    	$extends = dirname($filename).'/'.$extends;
                    $super = $this->parse($extends, true);
                    foreach ($super as $section => $value) {
                    		$section = (string) $section;
                        $data[$section] = isset($data[$section]) ? $this->array_merge($value, $data[$section]) : $value;
                    }
                }
                unset($data['extends']);
            }
        }
        
        return $data;
    }
    
    protected function array_merge($a, $b)
    {
		foreach ($b as $key => $value) {
            $a[$key] = $value;
        }
        
        return $a; 
    }
}

?>