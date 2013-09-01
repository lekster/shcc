<?php


require_once 'pbr-lib-common/src/Analytics/Btp/Stat/Connection.class.php';
require_once 'pbr-lib-common/src/Analytics/Btp/Stat/Request.class.php';
require_once 'pbr-lib-common/src/Analytics/Btp/Stat/Counter.class.php';


class PBR_Analytics_Btp_Sender
{
    private $_conn;
    
    public function __construct($host, $port)
    {
        //array('host'=>'udp://vps8147.mtu.immo','port'=>8888
        $this->_conn = PBR_Analytics_Btp_Stat_Btp_Request::getLast(array('host'=> $host,'port'=>$port));
    }
    
    public function sendData($sevice, $host, $operation, $timeUsec)
    {
        $counter = new PBR_Analytics_Btp_Stat_Btp_Counter($this->_conn, array(
            'ts' => floatval($timeUsec),
            'srv' => $host,
            'service' => $sevice,
            'op' => $operation,
        ));
        
    }
}

?>
