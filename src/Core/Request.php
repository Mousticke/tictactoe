<?php namespace Core;

class Params {
	private $params;
	
	public function __construct($params){
		$this->params = $params;
	}
	
	public function get($name, $default=null){
		if( $this->has($name) )
			return $this->params[$name];
		else
			return $default;
	}
	
	public function has($name){
		return isset($this->params[$name]);
	}
	
	public function req($name){
		if( ! $this->has($name) )
			throw new \Exception("Missing parameter: $name");
		
		return $this->get($name);
	}
}

class Request {
	private $method;
	private $path;
	private $query;
	private $params;
	
	public function __construct($method, $path, $query, $params){
		$this->method = $method;
		$this->path	  = $path;
		$this->query  = new Params($query);
		$this->params = new Params($params);
	}
	
	public function getMethod(){
		return $this->method;
	}
	
	public function getPath(){
		return $this->path;
	}
	
	public function getPathParts(){
		$parts = explode('/', $this->getPath());
		array_clean($parts);
		return $parts;
	}
	
	public function getQuery(){
		return $this->query;
	}
	
	public function getParams(){
		return $this->params;
	}
}

