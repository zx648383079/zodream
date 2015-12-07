<?php
namespace Zodream\Head\Route;

use Zodream\Body\Interfaces\IRoute;
use Zodream\Head\Url;

class Grace implements IRoute {
	public static function get() {
		$url = Url::request_uri();
		return end(explode('.php', explode('?', $url)[0]));
	}
	
	public static function to($file) {
		return rtrim(APP_URL, '/').'/'.ltrim($file, '/');
	}
}