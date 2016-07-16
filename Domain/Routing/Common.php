<?php
namespace Zodream\Domain\Routing;
/**
 * 普通链接方式 即 c v
 */
use Zodream\Infrastructure\DomainObject\RouterObject;
use Zodream\Infrastructure\Request;

class Common implements RouterObject {
	
	protected $controllerLabel = 'c';
	
	protected $actionLabel = 'a';
	
	public static function get() {
		$values = explode('/', Request::get('v' , 'index'));
		$action = array_shift($values);
		$args = array();
		for ($i = 0, $len = count($values); $i < $len; $i += 2) {
			$args[$i] = $values[$i + 1];
		}
		return array(
				str_replace('/', '\\', Request::get('c' , 'home')),
				$action,
				$args
		);
	}
	
	public static function to($file) {
		 
	}

	/**
	 * @return ResponseObject
	 */
	public function run() {
		// TODO: Implement run() method.
	}

	/**
	 * @return Route
	 */
	public function getRoute() {
		// TODO: Implement getRoute() method.
	}
}