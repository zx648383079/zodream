<?php
namespace Zodream\Domain\Routing;


use Zodream\Infrastructure\DomainObject\RouteObject;
class Grace implements RouteObject {
	public static function get() {
		$url = Url::request_uri();
		return end(explode('.php', explode('?', $url)[0]));
	}
	
	public static function to($file) {
		return rtrim(APP_URL, '/').'/'.ltrim($file, '/');
	}
}