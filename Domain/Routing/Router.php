<?php 
namespace Zodream\Domain\Routing;
/**
* 路由
* 
* @author Jason
*/
use Zodream\Infrastructure\Config;

defined('APP_CONTROLLER') or define('APP_CONTROLLER', Config::getInstance()->get('app.controller'));
defined('APP_ACTION')     or define('APP_ACTION', Config::getInstance()->get('app.action'));

class Router{
	
	/**
	 * 成功执行的method
	 * @var unknown
	 */
	public static $route;
	
	/**
	 * 运行加载方法
	 */
	public static function run() {
		$routes = call_user_func(array(RouteConfig::getInstance()->getDriver(), 'get'));
		if (is_string($routes)) {
			//执行已注册的
			if (self::_loopConfig($routes, RouteConfig::getInstance()->get())) {
				return self::$route;
			}
			$routes = self::_getValue($routes);
		}
		
		list($controller, $action, $values) = $routes;
		unset($routes);
		//执行默认的
		if (empty($controller) && empty($action)) {
			return self::$route = new Route(RouteConfig::getInstance()->getDefault(), $values, false);
		}
		//自动判断
		self::_autoload($controller, $action, $values);
	}
	
	/**
	 * 根据数字判断是否带值
	 * @param array $routes
	 * @return array:
	 */
	private function _getValue($url) {
		$url    = trim($url, '/');
		$routes = explode('/', $url);
		$values = array();
		for ($i = 0, $len = count($routes); $i < $len; $i++) {
			if (is_numeric($routes[$i])) {
				if (($len - $i) % 2 == 0) {
					// 数字作为分割符,无意义
					$values = array_splice($routes, $i + 1);
					unset($routes[$i]);
				} else {
					$values = array_splice($routes, $i - 1);
				}
				break;
			} else {
				$routes[$i] = ucfirst($routes[$i]);
			}
		}
		$args = array();
		for ($i = 0, $len = count($values); $i < $len; $i += 2) {
			$args[$values[$i]] = $values[$i + 1];
		}
		
		if (count($routes) == 1) {
			$action     = null;
			$controller = empty($routes[0]) ? null : $routes[0];
		} else {
			$action     = array_pop($routes);
			$controller = implode('\\', $routes);
		}
		return array(
				$controller,
				$action,
				$args
		);
	}
	
	/**
	 * 循环匹配已注册路由
	 * @param unknown $url
	 * @param unknown $config
	 * @param unknown $value
	 */
	private static function _loopConfig($url, $config) {
		foreach ($config as $key => $instance) {
			$pattern = str_replace(':num', '[0-9]+', $key);
			$pattern = str_replace(':any', '[^/]+', $pattern);
			$pattern = str_replace('/', '\\/', $pattern);
			$matchs  = array();
			preg_match('/'.$pattern.'/i', $url, $matchs);
			if(count($matchs) > 0 && array_shift($matchs) === $url) {
				$values = array();
				foreach ($matchs as $key => $value) {
					if (!is_numeric($key)) {
						$values[$key] = $value;
					}
				}
				self::$route = new Route($instance, $values, false);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 加载控制器和视图
	 *
	 * @access globe
	 * @param $c string 控制器的名称
	 * @param $v string 视图所在的方法名
	 */
	private static function _autoload($controller, $action, $values = array()) {
		if (empty($action)) {
			$action = 'index';
		}
		return self::$route = new Route($controller.'@'.$action, $values, true);
	}
	
	/**
	 * 
	 * @param string|array $route
	 * @param string $value
	 */
	public static function add($route, $value = null) {
		RouteConfig::getInstance()->set($route, $value);
	}
}