<?php
namespace Zodream\Service;
/**
* 启动
* 
* @author Jason
* @time 2015-12-19
*/
use Zodream\Domain\Routing\Router;

defined('VERSION') or define('VERSION', 2.0);
defined('APP_DIR') or define('APP_DIR', dirname(dirname(__FILE__)).'/');

class Application {
	/**
	 * 程序启动
	 */
	public static function main() {
		Router::run();
		Router::$route->run();
	}
	
}