<?php

require_once ("libraries/common/PhpConf/Config.php");

define('GIRAR_BASE_DIR', './');

/**
 * ������ ��� ������ PHP ��������.
 * ������ ��������� �������� �������� �� PHP ��������, � ����� ������ ���������� � ������������ ���������.
 * 
 * ������:
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
 * <b>����������� ������.</b><br>
 * ������������ ��� ���������������� ������ �� ���������� ������ ������. � ������� ����� ������ ������� �� ��������, �. �. ����������� ������ ���������� ����� �������� �����.<br>
 * ������:
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
 * ��������� ������ ��� ������������ ������� � ������: <b>select</b>, <b>next</b>.
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
     * ���������� �������� �� �������.
     * �������� ���������� �� �������� ������ � �����. ����� ����� ����� ������ �������������� ������ � ������������ ��������� � ������� ���������� search � replace. ��� ��������� ����� ���� ��� ��������, ��� � ���������. ���� �������� � ������� �� ������, ������������ NULL. 
     * 
     * @param string $key �������� �����
     * @param string $section �������� ������
     * @param mixed $search �������� ������
     * @param mixed $replace �������� �����
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
     * �������� ������ ��� ��������.
     * �������� ������� ��������. ���� ��������� ������ �� ����������, ������ �� ����������.
     * 
     * @param string $section ��� ������ ��� ��������
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
     * �������� �� ��������� ������.
     * ��� ������������ �������, ���������� ������� ������� ������ � ������� ������ select. ����� ���������� ���������� �������� ������, ������� � �������. ��� ���������� ����� ������, ������������ NULL. ����� NULL ������������, ���� ������ �� �������.
     * 
     * @param mixed $search �������� ������
     * @param mixed $replace �������� �����
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
     * ���������� ������ �� �������.
     * ���� ������ �� �������, ������������ NULL.
     * 
     * @param string $section ��� ������
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