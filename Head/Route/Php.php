<?php
namespace App\Head\Route;

use App\Body\Interfaces\IRoute;
use App\Body\Object\Str;
use App\Head\Url;

class Php implements IRoute {
	public static function get() {
		$url = Url::to();
		$arr = Str::toArray($url, '.php', 2, array('', '/home/index'));
		return end($arr);
	}
	
	public static function to($file) {
		return rtrim(APP_URL, '/').'index.php/'.ltrim($file, '/');
	}
}