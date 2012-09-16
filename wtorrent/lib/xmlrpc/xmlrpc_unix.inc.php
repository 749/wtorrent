<?php

require_once ('xmlrpc.inc.php');

class xmlrpc_unix_client extends xmlrpc_client {
	function &send($msg, $timeout = 0, $method = '') {
		if (empty ( $method ))
			$method = $this->method;
		if ($method != 'file' and $method != 'unix' and $method != 'socket')
			parent::send ( $msg, $timeout = 0, $method = '' );
		else {
			$r = & $this->sendPayloadUNIX ( $msg );
		}
		return $r;
	}
	
	function sendPayloadUNIX($msg){
		if(empty($msg->payload)) {
			$msg->createPayload($this->request_charset_encoding);
		}
		$payload = $msg->payload;
		$encoding_hdr = '';
		$op= "POST / HTTP/1.0\r\n" .
				'User-Agent: ' . $GLOBALS['xmlrpcName'] . ' ' . $GLOBALS['xmlrpcVersion'] . "\r\n" .
				'Host: localhost '. "\r\n" .
				'Content-Type: ' . $msg->content_type . "\r\nContent-Length: " .
				strlen($payload) . "\r\n\r\n" .
				$payload;
		
		$len = strlen( $payload );
		$headers = "CONTENT_LENGTH\0{$len}\0";
		$headers .= "SCGI\01\0";
		$len = strlen( $headers );
		$op = "$len:$headers,$payload";
		
		//$op= $payload;
		if($this->debug > 1) {
			print "\n---SENDING---\n" . $op . "\n---END---\n";
			// let the client see this now in case http times out...
			flush();
		}
		$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
		$result = socket_connect($socket, $this->path);
		if(!$result) {
			$this->errno=socket_last_error($socket);
			$this->errstr='Connect error: '.socket_strerror($this->errno);
			$r=new xmlrpcresp(0, $GLOBALS['xmlrpcerr']['http_error'], $this->errstr . ' (' . $this->errno . ')');
			return $r;
		}
		$result = socket_write ($socket, $op, strlen ($op));
		if(!$result) {
			$this->errstr='Write error';
			$r=new xmlrpcresp(0, $GLOBALS['xmlrpcerr']['http_error'], $this->errstr);
			return $r;
		}
		$ipd='';
		
		while($data=socket_read($socket,32768)) {
			// shall we check for $data === FALSE?
			// as per the manual, it signals an error
			$ipd.=$data;
		}
		socket_close($socket);
		
		$ipd = preg_replace('/\r/', '', $ipd);
		//print $pid;
		$lines = explode("\n", $ipd);
		$ipd='';
		foreach ($lines as $value ) {
			if(preg_match('/(^<|.*<)/', $value))
				$ipd .= $value."\n";
		}
		$r =& $msg->parseResponse($ipd, false, $this->return_type);
		return $r; 
	}
}

