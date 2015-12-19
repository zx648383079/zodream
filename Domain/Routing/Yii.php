<?php
namespace Zodream\Domain\Routing;


use Zodream\Infrastructure\DomainObject\RouteObject;
class Yii implements RouteObject {
	public static function get() {
		return Request::getInstance()->get('r', 'home/index');
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