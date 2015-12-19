<?php
namespace Zodream\Domain\Routing;


use Zodream\Infrastructure\DomainObject\RouteObject;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
class Common implements RouteObject {
	public static function get() {
		$values = explode('/', Request::getInstance()->get('v' , 'index'));
		$routes = array(
				'controller' => ArrayExpand::ucFirst(explode('/', Request::getInstance()->get('c' , 'home'))),
				'action'     => array_shift($values),
				'value'      => $values
		);
		return $routes;
	}
	
	public static function to($file) {
		 
	}
}