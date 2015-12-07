<?php
namespace Zodream\Head\Route;

use Zodream\Body\Interfaces\IRoute;
use Zodream\Body\Object\Str;
use Zodream\Head\Url;

class Php implements IRoute {
	public static function get() {
		$url = Url::request_uri();
		$arr = Str::toArray($url, '.php', 2, array('', '/home/index'));
		return end($arr);
	}
	
	public static function to($file) {
		return rtrim(APP_URL, '/').'index.php/'.ltrim($file, '/');
	}
}