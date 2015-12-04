<?php
namespace App\Head\Route;

use App\Body\Interfaces\IRoute;
use App\Body\Request;

class Yii implements IRoute {
	public static function get() {
		return Request::getInstance()->get('r', 'home/index');
	}
	
	public static function to($file) {
		return rtrim(APP_URL, '/').'/?r='.$file;
	}
}