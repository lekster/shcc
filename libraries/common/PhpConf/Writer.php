<?php



/**
 * ������ ��� ��������/��������� PHP ��������.
 * ������ ������, � ������� �� ������� ������, �� �������� � ������������ PHP ���������, ����� �� �������� ��������� ������������. ��� ��������� ���� ����������, ����������� ����� extend. ��� ������ ������, ���������� ������� ��������� �������, �������� ������ (set, extend) � ������� ����� save ��� ���������� ���������.
 * 
 * ������:
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
     * �����������.
     * ��������� PHP ������ ��� ������. ���� ������� ���, �������� ������� ������.
     * 
     * @param string $filename ��� �����
     * @throws PhpConf_Exception ���� ������� ��� ������� ���� �� �������
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
     * ������������� �������� ������ � �����.
     * 
     * @param string $section ��� ������    
     * @param string $key ��� �����
     * @param mixed $value ��������
     * @return void
     */
    public function set($section, $key, $value)
    {
       $this->data[$section][$key] = $value;
    }
    
    /**
     * ������� �������� ���� ������ ��� �����.
     * ���� ��� ����� �� �������, ��������� ��� ������.
     * 
     * @param string $section ��� ������
     * @param string $key ��� �����
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
     * ������� ������.
     *
     * @return void
     */
    public function clear()
    {
        $this->data = array();
    }    
    
    /**
     * ������������� ��� ������������ ����������������� �����.
     * ���������� ���������� ������������ ������� ������� ����������������� �����. ���� ��� ����� �� ������� ��� ���������� FALSE, ���������� ���������.
     * 
     * @param string $filename �������� ������������ �����
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
     * ��������� PHP ������ � ����.
     * �� ��������� � ��������, ���� ������ �������� filename, ��������� �����.
     * 
     * @param string $filename ��� ����� ��� ����������
     * @throws PhpConf_Exception ���� ������ �� �������
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