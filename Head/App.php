<?php
namespace App\Head;
/*
* 全局方法
* 
* @author Jason
* @time 2015-11.29
*/
defined('VERSION') or define('VERSION', 1.0);

class App {
	public static function main() {
		Route::run();
	}
}