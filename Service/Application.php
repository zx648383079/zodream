<?php
namespace Zodream\Service;
/**
* 启动
* 
* @author Jason
* @time 2015-12-19
*/
use Zodream\Domain\Html\VerifyCsrfToken;
use Zodream\Domain\Routing\Router;
use Zodream\Domain\Autoload;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Cookie;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\EventManager\EventManger;
use Zodream\Infrastructure\Session;

defined('VERSION') or define('VERSION', 2.0);
defined('APP_DIR') or define('APP_DIR', dirname(dirname(__FILE__)).'/');

class Application {
	/**
	 * 程序启动
	 */
	public static function main() {
		Autoload::getInstance()->setError()->shutDown();
		EventManger::getInstance()->run('appRun');
		//修复有时候 cookie 的第一个键名出现异常字符
		foreach ($_COOKIE as $key => $value) {
			if (strpos($key, ',_') !== false) {
				$_COOKIE[str_replace(',_', '', $key)] = $value;
			}
		}
		if (Config::getInstance()->get('safe.csrf', false) && !Request::isGet()) {
			VerifyCsrfToken::verify();
		}
		Router::run();
	}
}