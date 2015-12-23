<?php
namespace Zodream\Domain\Routing;


use Zodream\Infrastructure\DomainObject\RouteObject;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
class PathInfo implements RouteObject {
	public static function get() {
		$url = UrlGenerator::getUri();
		$arr = StringExpand::toArray($url, '.php', 2, array('', '/home/index'));
		return end($arr);
	}
	
	public static function to($file) {
		return rtrim(APP_URL, '/').'index.php/'.ltrim($file, '/');
	}
}