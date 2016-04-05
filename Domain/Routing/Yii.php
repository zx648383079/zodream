<?php
namespace Zodream\Domain\Routing;
/**
 * 以 r 参数带链接
 */
use Zodream\Infrastructure\DomainObject\RouteObject;
use Zodream\Infrastructure\Request;
class Yii implements RouteObject {
	public static function get() {
		return Request::get('r', 'home/index');
	}
	
	public static function to($file) {
		$root = rtrim(APP_URL, '/') .'/';
		if (!strpos($file, '.php')) {
			$root .= '/?r=';
		}
		$root .= ltrim($file, '/');
		return $root;
	}
}