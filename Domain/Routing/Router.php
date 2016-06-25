<?php 
namespace Zodream\Domain\Routing;
/**
* 路由
* 
* @author Jason
*/
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\EventManager\EventManger;

defined('APP_CONTROLLER') or define('APP_CONTROLLER', Config::getInstance()->get('app.controller'));
defined('APP_ACTION')     or define('APP_ACTION', Config::getInstance()->get('app.action'));
defined('APP_MODEL')      or define('APP_MODEL', Config::getInstance()->get('app.model'));
defined('APP_FORM')       or define('APP_FORM', Config::getInstance()->get('app.form'));

class Router{
	
	/**
	 * 成功执行的method
	 * @var Route
	 */
	private static $_route;

	/**
	 * 获取路由
	 * @return Route
	 */
	public static function getRoute() {
		return self::$_route;
	}

	/**
	 * 设置路由
	 * @param Route $route
	 */
	public static function setRoute($route) {
		self::$_route = $route;
	}

	/**
	 * 判断是否有路由
	 * @return bool
	 */
	public static function hasRoute() {
		return !empty(self::$_route);
	}

	/**
	 * 获取路由中的执行类和方法
	 * @return array|null
	 */
	public static function getClassAndAction() {
		if (self::hasRoute()) {
			return self::getRoute()->getClassAndAction();
		}
		return null;
	}

	/**
	 * 路由加载及运行方法
	 * @return null
	 */
	public static function run() {
		EventManger::getInstance()->run('getRoute');
		self::_getRouteByDriver();
		if (self::hasRoute()) {
			return self::getRoute()->run();
		}
		return null;
	}

	/**
	 * 通过配置的驱动获取路由
	 */
	private static function _getRouteByDriver() {
		self::_runByRoute(call_user_func(array(RouteConfig::getInstance()->getDriver(), 'get')));
	}

	/**
	 * 根据路由判断
	 * @param array|string $routes
	 */
	private static function _runByRoute($routes) {
		if (is_string($routes)) {
			$routes = trim($routes, '/');
		}
		if (is_string($routes)) {
			self::_loopConfig($routes, RouteConfig::getInstance()->get());
			return;
		}
		self::_runByRouteWhenArray($routes);
	}

	/**
	 * 当获取到的路由是array数组
	 * @param array $routes
	 */
	private static function _runByRouteWhenArray(array $routes) {
		list($controller, $action, $values) = $routes;
		//执行默认的
		if (empty($controller) && empty($action)) {
			self::setRoute(new Route(RouteConfig::getInstance()->getDefault(), $values, false));
			return;
		}
		//自动判断
		self::_autoload($controller, $action, $values);
	}


	
	/**
	 * 从字符串中分离class,action,value
	 * @param string $route
	 * @return array (class, action, values)
	 */
	private static function _getRoutesWhenString($route) {
		list($routes, $values) = self::_spiltArrayByNumber(explode('/', trim($route, '/')));
		if (count($routes) == 1) {
			$action     = null;
			$controller = empty($routes[0]) ? null : $routes[0];
		} else {
			$action     = strtolower(array_pop($routes));
			$controller = implode('\\', $routes);
		}
		return array(
				$controller,
				$action,
				$values
		);
	}

	/**
	 * 根据数字值分割数组
	 * @param array $routes
	 * @return array (routes, values)
	 */
	private static function _spiltArrayByNumber(array $routes) {
		$values = array();
		for ($i = 0, $len = count($routes); $i < $len; $i++) {
			if (is_numeric($routes[$i])) {
				if (($len - $i) % 2 == 0) {
					// 数字作为分割符,无意义
					$values = array_splice($routes, $i + 1);
					unset($routes[$i]);
				} else {
					$routes[$i - 1] = strtolower($routes[$i - 1]);
					$values = array_splice($routes, $i - 1);
				}
				break;
			} else {
				$routes[$i] = ucfirst($routes[$i]);
			}
		}
		return array(
			$routes,
			self::_pairValues($values)
		);
	}

	/**
	 * 将索引数组根据单双转关联数组
	 * @param $values
	 * @return array
	 */
	private static function _pairValues($values) {
		$args = array();
		for ($i = 0, $len = count($values); $i < $len; $i += 2) {
			if (isset($values[$i + 1])) {
				$args[$values[$i]] = $values[$i + 1];
			}
		}
		return $args;
	}
	
	/**
	 * 循环匹配已注册路由
	 * @param string $route
	 * @param array $config
	 */
	private static function _loopConfig($route, $config) {
		foreach ($config as $key => $instance) {
			$pattern = str_replace(':num', '[0-9]+', $key);
			$pattern = str_replace(':any', '[^/]+', $pattern);
			$pattern = str_replace('/', '\\/', $pattern);
			$matches  = array();
			preg_match('/'.$pattern.'/i', $route, $matches);
			if(count($matches) > 0 && array_shift($matches) === $route) {
				$values = array();
				foreach ($matches as $k => $value) {
					if (!is_numeric($k)) {
						$values[$k] = $value;
					}
				}
				self::setRoute(new Route($instance, $values, false));
				return;
			}
		}
		self::_runByRouteWhenArray(self::_getRoutesWhenString($route));
	}
	
	/**
	 * 加载控制器和视图
	 * @param string $controller 控制器的名称
	 * @param string $action 视图所在的方法名
	 * @param array $values 值
	 */
	private static function _autoload($controller, $action, $values = array()) {
		if (empty($action)) {
			$action = 'index';
		}
		self::setRoute( new Route($controller.'@'.$action, $values, true));
	}
	
	/**
	 * 手动注册路由
	 * @param string|array $route
	 * @param string $value
	 */
	public static function add($route, $value = null) {
		RouteConfig::getInstance()->set($route, $value);
	}
}