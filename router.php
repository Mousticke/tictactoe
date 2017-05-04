<?php

require('config.php');
require('utils.php');

// Setup output buffering and the session, once and for all.
ob_start( NULL, 0 );
session_start();

// This class sets up class auto-load so we don't have to manually
// include everything in every file.
class Autoloader {
	public static function classNameToFileName($class){
		// PHP namespaces are deimited by backslashes, but the filesystem
		// wants slashes, so do the conversion.
		$class = str_replace('\\', '/', $class);
		
		// Add the file extension
		return $class . '.php';
	}
	
	public static function autoloadHandler($name){
		// Find out where the corresponding file is
		$fileName = self::classNameToFileName($name);
		$filePath = CLASSPATH . '/' . $fileName;
		
		// Make sure it exists, and include it
		if( is_readable($filePath) ){
			require_once($filePath);
			return;
		} else {
			die("Couldn't load class '$name' from file '$filePath'");
		}
	}
	
	public static function start(){
		spl_autoload_register( 'Autoloader::autoloadHandler' );
	}
};

// This class encapsulates the setup process
class Server {
	public static function findRoot(){
		// Find where on the filesystem we're installed
		$absPath = str_replace('\\', '/', dirname(__FILE__)) . '/';
		define('ABSPATH', $absPath);
		
		// Find the installation path, relative to server root
		$selfPath = $_SERVER['PHP_SELF'];
		$hostBase = substr($selfPath, 0, strlen($selfPath) - strlen('router.php'));
		define('HOSTBASE', $hostBase);
	}
	
	public static function start($rootEndPoint){
		// Find the install root
		self::findRoot();
		
		// Create the router
		$router = new \Core\Router($rootEndPoint);
		$router->handleRequest();
	}
}

Autoloader::start();
Server::start(new \API\RootEndPoint());
