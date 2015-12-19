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
	public static $method;
	
	/**
	 * 运行加载方法
	 */
	public static function run() {
		$instance = call_user_func(array(RouteConfig::getInstance()->getDriver(), 'get'));
		if (!is_array($instance)) {
			$instance = self::_getValue($instance);
		}
		$controllers = $instance['controller'];
		$action      = $instance['action'];
		$value       = $instance['value'];
		unset($instance);
		//执行默认的
		if (empty($controllers) && empty($action)) {
			return new Route(RouteConfig::getInstance()->get('default'), $value);
		}
		//执行已注册的
		$url = implode('/', $controllers);
		if (!empty($action)) {
			$url .= '/'.$action;
		}
		self::_loopConfig($url, RouteConfig::getInstance()->get(), $value);
		//自动判断
		self::_autoload($controllers, $action, $value);
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
				$values = array_splice($routes, $i);
				break;
			}
		}
		return array(
				'action'     => (count($routes) > 1) ? array_pop($routes) : null,
				'controller' => array_map('ucfirst', $routes),
				'value'      => $values
		);
	}
	
	/**
	 * 循环匹配已注册路由
	 * @param unknown $url
	 * @param unknown $config
	 * @param unknown $value
	 */
	private static function _loopConfig($url, $config, $value = array()) {
		foreach ($config as $key => $instance) {
			$pattern = str_replace(':num', '[0-9]+', $key);
			$pattern = str_replace(':any', '[^/]+', $pattern);
			$pattern = str_replace('/', '\\/', $pattern);
			$matchs  = array();
			preg_match('/'.$pattern.'/i', $url, $matchs);
			if(count($matchs) > 0 && array_shift($matchs) === $url) {
				return new Route($instance, array_merge($matchs, (array)$value));
			}
		}
	}
	
	/**
	 * 加载控制器和视图
	 *
	 * @access globe
	 * @param $c string 控制器的名称
	 * @param $v string 视图所在的方法名
	 */
	private static function _autoload($controllers, $action, $values = array()) {
		if (empty($action)) {
			$action = 'index';
		}
		$instance = implode('\\', $controllers);
		return new Route($instance.'@'.$action, $values);
	}
	
	/**
	 * 
	 * @param string|array $route
	 * @param string $value
	 */
	public static function add($route, $value = null) {
		static::getInstance()->set($route, $value);
	}
}