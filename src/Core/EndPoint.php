<?php namespace Core;

use Core\Request;

abstract class EndPoint {
	public abstract function resolve(Request $request);
}

