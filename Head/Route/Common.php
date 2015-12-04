<?php
namespace App\Head\Route;

use App\Body\Interfaces\IRoute;
use App\Body\Request;
use App\Body\Object\Arr;

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