<?php



/**
 * Объект для создания/изменения PHP конфигов.
 * Объект записи, в отличии от объекта чтения, не работает с расширяемыми PHP конфигами, чтобы не нарушать структуру наследования. Для изменения пути расширения, используйте метод extend. Для начала работы, необходимо создать экземпляр объекта, изменить данные (set, extend) и вызвать метод save для сохранения изменений.
 * 
 * Пример:
 * <code>
 * try {
 *  $writer = new PhpConf_Writer('default.conf');
 *  $writer->set('section', 'key', 'value');
 *  $writer->save();
 * } catch (PhpConf_Exception $e) {
 *  die($e->getMessage());
 * }
 * </code>
 * 
 * @version 1.1.0
 * @copyright Copyright Inform-mobil 2007
 * @author vadim
 */
class PhpConf_Writer extends PhpConf_Reader
{
    /**
     * Конструктор.
     * Открывает PHP конфиг для записи. Если конфига нет, пытается создать пустой.
     * 
     * @param string $filename имя файла
     * @throws PhpConf_Exception если открыть или создать файл не удалось
     */
    public function __construct($filename = false)
    {
    		if ($filename) {
    			
	          if (!$fp = @fopen(($filename[0] != '/' ? './config/' : '').$filename, 'rb', true)) {
	              $this->save(($filename[0] != '/' ? './config/' : '').$filename);
	          } else {
	              fclose($fp);
	          }
	        
	          parent::__construct($filename, false);
	      }
    }
    
    /**
     * Устанавливает значение секции и ключа.
     * 
     * @param string $section имя секции    
     * @param string $key имя ключа
     * @param mixed $value значение
     * @return void
     */
    public function set($section, $key, $value)
    {
       $this->data[$section][$key] = $value;
    }
    
    /**
     * Удаляет значение всей секции или ключа.
     * Если имя ключа не указано, удаляется вся секция.
     * 
     * @param string $section имя секции
     * @param string $key имя ключа
     * @return void
     */
    public function del($section, $key = false)
    {
        if ($key) {
            unset($this->data[$section][$key]);
        } else {
            unset($this->data[$section]);
        }
    }
    
    /**
     * Очищает конфиг.
     *
     * @return void
     */
    public function clear()
    {
        $this->data = array();
    }    
    
    /**
     * Устанавливает имя расширяемого конфигурационного файла.
     * Расширение происходит относительно текущей локации конфигурационного файла. Если имя файла не указано или передается FALSE, расширение удаляется.
     * 
     * @param string $filename название расширяемого файла
     */
    public function extend($filename = false)
    {
        if ($filename) {
            $this->data['extends'] = $filename;
        } else {
            unset($this->data['extends']);
        }
    }
    
    /**
     * Сохраняет PHP конфиг в файл.
     * По умолчанию в исходный, если указан параметр filename, создается копия.
     * 
     * @param string $filename имя файла для сохранения
     * @throws PhpConf_Exception если запись не удалась
     */
    public function save($filename = false)
    {
        if (!$filename) $filename = './config/'.$this->filename;
        if (!$fp = @fopen($filename, 'w', true)) throw new PhpConf_Exception('could not open file: "'.$filename.'" for writing', PhpConf_Config::EXC_WRITEFAIL);
        if (!@fwrite($fp, "<?php\n\n/**\n * WARNING: this file was created or\n * modified with PhpConf_Writer,\n * all manual changes may be lost!\n */\n\nreturn ")) throw new PhpConf_Exception('could not write to file: "'.$filename.'"', PhpConf_Config::EXC_WRITEFAIL);
        if (!@fwrite($fp, var_export($this->data, true).';')) throw new PhpConf_Exception('could not write to file: "'.$filename.'"', PhpConf_Config::EXC_WRITEFAIL);
        if (!@fwrite($fp, "\n\n?>\n")) throw new PhpConf_Exception('could not write to file: "'.$filename.'"', PhpConf_Config::EXC_WRITEFAIL);
        fclose($fp);
    }
}

?>