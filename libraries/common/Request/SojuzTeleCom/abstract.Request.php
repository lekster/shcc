<?php

require_once 'libraries/internal/php5/mobile-commerce/common/src/Request/SojuzTeleCom/class.Check.php';
require_once 'libraries/internal/php5/mobile-commerce/common/src/Request/SojuzTeleCom/class.Confirm.php';
require_once 'libraries/internal/php5/mobile-commerce/common/src/Request/SojuzTeleCom/class.Status.php';

/**
 * ����������� ����� Immo_MobileCommerce_SojuzTeleCom_Request
 *
 * @copyright Inform-Mobil
 * @author Vadim Kurochkin <kurochkin@immo.ru>
 */
 
/*
���� ������ �������� �������� check

���������������� ��������� ������� � �������� ��� ��������� �������� � �������

� ��� ������ ����
id
phone
datetime
shortphone
msgbody
control
cmd

�������� ����� �� ���������� � �������


// ��������� POST (��� GET), ���������� XML
// ����� �� ���� ������ �� �������� �������������� ������, ������� ����� �������� � ��������� ���������?

// �������� ���� ������ ������� ��������� � ������������� �������

// ������� ������ �� ������������. ���� �����-�� ������, ������� ������ � ������ action = check, confirm, status - ������� ��� �����������
// check. ���� ������� ������ � �������� - ��������� ������������� ��
	// �������� ��������� msgbody. ��, ��� [����] [������� ���� � �����-�� �������] [����� ����� � �����-�� �������] - ������� ��� �����
	// ok
		// ������������ ������ ��� http-�������: URL, ���� ������� - ������� ��� �����
		// next
			// ����������, �������� http-������ - �����, ����� ������� ��������� �������������
			// ok
				// ��������� �����. ������� ���� ����������: ok|no|invalid_response - ������� ��� �����
				// ok
					// �������� ������������, ��� �� ���������� - �������� ������ - OK - ������� ��� �����������
				// no
					// �������� ������������, ��� �� �� ���������� - �������� ������ - NOT OK - ������� ��� �����������
				// invalid_response
					// ���� �������� ������������, ��� ������, ���������� �����.
			// no ���� �������� ������������, ��� ������, ���������� �����.
	// no
		// �������� ������������, ��� ������ ������� �� ������������� ���������� - ������������ ������
// confirm. ������������� �� ������
	// �������� ��������� ������� - ����� ���
	// ok
		// �������� ���������� �� ��
		// next
			// ��������� ������ - ������� ��� ��� - ������� ��� �����
			// ok (�������)
				// �������� ������ ����������, ����� ������, ...
			// no (�� �������)
				// �������� ������ ����������, ����� ������, ...
		
	// no
		// �������� ������������, ��� ������ ������� �� ������������� ���������� - ������������ ������

���� ������ ���������� �� �������� �������|��������� � �������� ������ ��������.
���� ������ ���������� �� �������, ���� ��������� ������ � �������.

����� �������� Response

<?xml version="1.0" encoding="UTF-8"?>
<response>
<result>RESULT</result>
<info>ORDER_INFO</info>
<sum>SUM</sum>
<order>ORDER</order>
<answer>ANS</answer>
<descr>DESCRIPTION</descr>
</response>

*/

abstract class Immo_MobileCommerce_SojuzTeleCom_Request
{
    protected $_id;
    protected $_phone;
    // protected $_result;
    protected $_control;
    protected $_cmd;
    
    protected $_commands = array('check', 'confirm', 'status');
    
    public function __contruct($xmlStr)
    {
        $this->init($xmlStr);
    }
    
    public function init($xmlStr)
    {
        $xml = new SimpleXMLElement($xmlStr);
        $this->_id      = $xml->id;
        $this->_phone   = $xml->phone;
        // $this->_result  = $xml->result;
        $this->_control = $xml->control;
        $this->_cmd     = $xml->cmd;
    }
    
    public static function factory($xmlSrc)
    {
        $xml = new SimpleXMLElement($xmlStr);
        if (empty($xml->cmd) || !in_array($xml->cmd, $this->_commands))
        {
            throw new Immo_MobileCommerce_Exception('Invalid SojuzTeleCom request command: '.var_export($xml->cmd, true));
        }
        //return new {'Immo_MobileCommerce_SojuzTeleCom_Request_'.ucfirst($xml->cmd)}($xmlStr);
    }
    
    public function getId()
    {
        return $this->_id;
    }
    
    public function getPhone()
    {
        return $this->_phone;
    }
    
    // public function getResult()
    // {
        // return $this->_result;
    // }
    
    public function getControl()
    {
        return $this->_control;
    }
    
    public function getCmd()
    {
        return $this->_cmd;
    }
}
