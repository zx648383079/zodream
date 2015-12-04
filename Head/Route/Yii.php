<?php
namespace Zodream\Head\Route;

use Zodream\Body\Interfaces\IRoute;
use Zodream\Body\Request;

class Yii implements IRoute {
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