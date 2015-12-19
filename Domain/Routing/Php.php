<?php
namespace Zodream\Domain\Routing;


use Zodream\Infrastructure\DomainObject\RouteObject;
class Php implements RouteObject {
	public static function get() {
		$url = Url::request_uri();
		$arr = Str::toArray($url, '.php', 2, array('', '/home/index'));
		return end($arr);
	}
	
	public static function to($file) {
		return rtrim(APP_URL, '/').'index.php/'.ltrim($file, '/');
	}
}