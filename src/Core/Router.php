<?php namespace Core;

use Core\Request;
use Core\Response;

use Exception;

class Router {
	private $root;
	
	// Retrieves the URL path, relative to the site root
	static private function getRelativePath(){
		// Make sure the server has enough support for us to do this
		if( ! isset($_SERVER['REQUEST_URI']) )
			fatal( 'Unsupported server:  $_SERVER["REQUEST_URI"] is not defined' );
		
		// Retrieve the URI, relative to server root
		$requestUri = urldecode( $_SERVER['REQUEST_URI'] );
		
		// Subtract the site's base URL to get a relative URL
		return substr( $requestUri, strlen(HOSTBASE) );
	}
	
	public function __construct($root){
		$this->root = $root;
	}
	
	public function handleRequest(){
		$request = $this->buildRequest();
		$response = $this->routeRequest($request);
		$response->send();
	}
	
	private function buildRequest(){
		$method = $_SERVER['REQUEST_METHOD'];
		$path   = self::getRelativePath();
		$query  = $_GET;
		$params = [];
		
		switch($method){
			// POST request, PHP has built-in support for that
	 		case 'POST':
	 			$params = $_POST;
	 			break;
 			
 			// These methods can also include data, but there's no built-in
 			// support for that, so we have to parse them ourselves.
 			// Ref: https://lornajane.net/posts/2008/Accessing-Incoming-PUT-Data-from-PHP#c1559
 			// Ref: http://stackoverflow.com/questions/5374796/get-a-php-delete-variable
 			case 'PUT':
 			case 'PATCH':
 			case 'DELETE': {
 				if( isset($_SERVER['CONTENT_TYPE']) )
					$contentType = $_SERVER['CONTENT_TYPE'];
				else
					$contentType = '';
 				
 				if( $contentType == 'application/x-www-form-urlencoded' ){
	 				$data = file_get_contents('php://input');
	 				parse_str($data, $params);
 				}
 				
 				break;
 			}
		}
		
		return new Request($method, $path, $query, $params);
	}
	
	private function routeRequest($request){
		try {
			$response = $this->root->resolve($request);
			
			if( $response == null )
				$response = $this->make404Response();
			
			return $response;
		} catch(Exception $e){
			// If exceptions make it through, return a generic error message.
			// Not doing this will result in PHP printing out the message of
			// the exception, which might contain confidential data.
			// 
			// If a logging system exists, it'd be wise to log the detailed
			// error here to ease debugging.
			return $this->make500Response();
		}
	}
	
	private function make404Response(){
		$r = new Response();
		$r->setStatus(404);
		$r->printf("Page not found");
		return $r;
	}
	
	private function make500Response(){
		$r = new Response();
		$r->setStatus(500);
		$r->printf("Server error");
		return $r;
	}
}