<?php
namespace Zodream\Head;
/**
* 全局方法
* 
* @author Jason
* @time 2015-11.29
*/
defined('VERSION') or define('VERSION', 1.0);
defined('APP_DIR') or define('APP_DIR', dirname(dirname(__FILE__)).'/');
defined('APP_API') or define('APP_API', false);

class App {
	/**
	 * 程序启动
	 */
	public static function main() {
		date_default_timezone_set('Etc/GMT-8');            //这里设置了时区
		Route::run();
	}
	
}