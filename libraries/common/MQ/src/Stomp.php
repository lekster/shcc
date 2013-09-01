<?php
/* vim: set expandtab tabstop=3 shiftwidth=3: */
/*
 * A StompFrame are the messages that are sent and received on a StompConnection.
 *
 * @package Stomp
 * @author Hiram Chirino <hiram@hiramchirino.com>
 * @version $Revision$
 */
class StompFrame
{
	var $command;
	var $headers;
	var $body;

	public function StompFrame($command = null, $headers = null, $body = null)
	{
		$this->command = $command;
		$this->headers = $headers;
		$this->body = $body;
	}
}

/**
 * A Stomp Connection
 *
 * The class wraps around HTTP_Request providing a higher-level
 * API for performing multiple HTTP requests
 *
 * @package Stomp
 * @author Hiram Chirino <hiram@hiramchirino.com>
 * @version $Revision$
 */
class StompConnection
{
	public $socket;
	//При отладке установить в true (будет выводить вардампы)
	private $_debug = false;

	public function __construct($host, $port = 61613)
	{
		$this->socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
		$result = socket_connect($this->socket, $host, $port) or die("Could not connect to server\n");
	}

	public function connect($userName = "", $password = "", $heartbeat = "")
	{
		$this->writeFrame(new StompFrame("CONNECT", array("login" => $userName, "passcode" => $password, 'heart-beat' => $heartbeat)));
		return $this->readFrame();
	}

	public function send($destination, $body, $properties = null)
	{
		$headers = array();
		if(isset($properties))
			foreach($properties as $name => $value)
				$headers[$name] = $value;

		$headers["destination"] = $destination;
		if($this->_debug) var_dump('send');
		$this->writeFrame(new StompFrame("SEND", $headers, $body));
		if($this->_debug) var_dump('send ok');
	}

	public function subscribe($destination, $properties = null)
	{
		$headers = array("ack" => "client");
		if(isset($properties))
			foreach($properties as $name => $value)
				$headers[$name] = $value;

		$headers["destination"] = $destination;
		if($this->_debug) var_dump('subscribe');
		$this->writeFrame(new StompFrame("SUBSCRIBE", $headers));
		if($this->_debug) var_dump('subscribe ok');
	}

	public function unsubscribe($destination, $properties = null)
	{
		$headers = array();
		if(isset($properties))
			foreach($properties as $name => $value)
				$headers[$name] = $value;

		$headers["destination"] = $destination;
		if($this->_debug) var_dump('unsubscribe');
		$this->writeFrame(new StompFrame("UNSUBSCRIBE", $headers));
		if($this->_debug) var_dump('unsubscribe ok');
	}

	public function begin($transactionId = null)
	{
		$headers = array();
		if(isset($transactionId))
			$headers["transaction"] = $transactionId;

		if($this->_debug) var_dump('begin');
		$this->writeFrame(new StompFrame("BEGIN", $headers));
		if($this->_debug) var_dump('begin ok');
	}

	public function commit($transactionId = null)
	{
		$headers = array();
		if(isset($transactionId))
			$headers["transaction"] = $transactionId;

		if($this->_debug) var_dump('commit');
		$this->writeFrame(new StompFrame("COMMIT", $headers));
		if($this->_debug) var_dump('commit ok');
	}

	public function abort($transactionId = null)
	{
		$headers = array();
		if(isset($transactionId))
			$headers["transaction"] = $transactionId;

		if($this->_debug) var_dump('abort');
		$this->writeFrame(new StompFrame("ABORT", $headers));
		if($this->_debug) var_dump('abort ok');
	}

	public function acknowledge($messageId, $transactionId = null)
	{
		$headers = array();
		if(isset($transactionId))
			$headers["transaction"] = $transactionId;

		$headers["message-id"] = $messageId;
		if($this->_debug) var_dump('acknowledge');
		$this->writeFrame(new StompFrame("ACK", $headers));
		if($this->_debug) var_dump('acknowledge ok');
	}

	public function nacknowledge($messageId, $transactionId = null)
	{
		$headers = array();
		if(isset($transactionId))
			$headers["transaction"] = $transactionId;

		$headers["message-id"] = $messageId;
		$headers["requeue"] = "false";
		if($this->_debug) var_dump('nacknowledge');
		$this->writeFrame(new StompFrame("NACK", $headers));
		if($this->_debug) var_dump('nacknowledge ok');
	}

	public function disconnect()
	{
		if($this->_debug) var_dump('disconnect');
		$this->writeFrame(new StompFrame("DISCONNECT"));
		socket_close($this->socket);
	}

	public function writeFrame($stompFrame)
	{
		$data = $stompFrame->command . "\n";
		if(isset($stompFrame->headers))
			foreach($stompFrame->headers as $name => $value)
			{
				if($this->_debug) var_dump($value);
				$data .= $name . ": " . $value . "\n";
			}
		$data .= "\n";

		if(isset($stompFrame->body))
			$data .= $stompFrame->body;

		$l1 = strlen($data);
		$data .= "\x00\n";
		$l2 = strlen($data);

		if($this->_debug)
		{
			var_dump('w');
			var_dump($data);
			var_dump('w------------------------------');
		}
		socket_write($this->socket, $data, strlen($data)) or die("Could not send stomp frame to server\n");
	}

	public function readFrame()
	{
		//socket_set_timeout($this->socket, 2);
		socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec"	 => 7, "usec"	 => 0));
		$b		 = '';
		$rc		 = socket_recv($this->socket, &$b, 1, 0);
		// I think this EOF
		if($rc == 0)
			return null;

		// I think this is no data.
		if($rc == false)
			return null;

		$data = NULL;

		// Read until end of frame.
		while(ord($b) != 0)
		{
			$data .= $b;
			$t = ord($b);
			$rc = socket_recv($this->socket, &$b, 1, 0);
			// I think this EOF
			if($rc == 0)
				return null;
		}

		list($header, $body) = explode("\n\n", $data, 2);
		$header = explode("\n", $header);
		$headers = array();
		$command = null;

		foreach($header as $v)
		{
			if(isset($command))
			{
				list($name, $value) = explode(':', $v, 2);
				$headers[$name] = $value;
			}
			else
				$command = $v;
		}

		$ret = new StompFrame($command, $headers, $body);
		if($this->_debug) var_dump($ret);
		return $ret;
	}
}