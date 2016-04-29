<?php
namespace Zodream\Domain\Routing;
/**
 * 后缀链接 index.php/home 优雅链接已包含此项
 */
use Zodream\Infrastructure\DomainObject\RouteObject;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
class PathInfo implements RouteObject {
	public static function get() {
		$url = Url::getUri();
		$arr = StringExpand::explode($url, '.php', 2, array('', '/home/index'));
		return $arr[1];
	}
	
	public static function to($file) {
		return rtrim(APP_URL, '/').'index.php/'.ltrim($file, '/');
	}
}