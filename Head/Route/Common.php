<?php
namespace Zodream\Head\Route;

use Zodream\Body\Interfaces\IRoute;
use Zodream\Body\Request;
use Zodream\Body\Object\Arr;

class Common implements IRoute {
	public static function get() {
		$values = explode('/', Request::getInstance()->get('v' , 'index'));
		$routes = array(
				'controller' => Arr::ucFirst(explode('/', Request::getInstance()->get('c' , 'home'))),
				'action'     => array_shift($values),
				'value'      => $values
		);
		return $routes;
	}
	
	public static function to($file) {
		 
	}
}