<?php
namespace Zodream\Domain\Routing;
/**
 * 优雅链接
 */
use Zodream\Infrastructure\DomainObject\RouteObject;
class Grace implements RouteObject {
	public static function get() {
		$urlParams = explode('.php', UrlGenerator::getUriWithoutParam());
		return end($urlParams);
	}
	
	public static function to($file) {
		return rtrim(APP_URL, '/').'/'.ltrim($file, '/');
	}
}