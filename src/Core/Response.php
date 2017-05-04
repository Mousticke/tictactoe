<?php namespace Core;

class Response {
	private $status;
	private $headers;
	private $body;
	
	public function __construct(){
		$this->status = 200;
		$this->headers = array();
		$this->body = '';
	}
	
	public function getStatus(){
		return $this->status;
	}
	
	public function setStatus( $status ){
		$this->status = $status;
	}
	
	public function hasHeader( $name ){
		return isset( $this->headers[$name] );
	}
	
	public function getHeader( $name ){
		return $this->headers[$name];
	}
	
	public function setHeader( $name, $value ){
		$this->headers[$name] = $value;
	}
	
	public function getBody(){
		return $this->body;
	}
	
	public function setBody( $body ){
		$this->body = $body;
	}
	
	public function append( $text ){
		$this->body .= $text;
	}
	
	public function printf(){
		$args = func_get_args();
		$fmt = array_shift($args);
		$this->append( vsprintf( $fmt, $args ) );
	}
	
	public function redirect( $url ){
		$this->setStatus( 301 );
		$this->setHeader( 'Location', $url );
	}
	
	public final function send(){
		// Send the status code
		http_response_code( $this->status );
		
		// Send every header
		foreach( $this->headers as $name => $value )
			header( "$name: $value" );
		
		// Send the body of the response
		print( $this->body );
	}
	
	public static function makeJSON($obj){
		$response = new Response();
		$response->json($obj);
		return $response;
	}
	
	public function json($obj, $status=200){
		$this->setStatus($status);
		$this->setHeader('Content-Type', 'application/json');
		$this->setBody(json_encode($obj));
	}
}

