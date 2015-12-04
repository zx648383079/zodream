<?php 
namespace Zodream\Head;
/**
* 路由
* 
* @author Jason
* @time 2015-12-3
*/

use Zodream\Body\Object\Obj;
use Zodream\Body\Config;
use Zodream\Body\Error;

class Route extends Obj{
	/**
	 * 运行加载方法
	 */
	public static function run() {
		$config   = Config::getInstance()->get('route');
		$instance = call_user_func(array($config['driver'], 'get'));
		$default  = $config['default'];
		unset($config['driver'], $config['default']);
		
		if (!is_array($instance)) {
			$instance = self::_getValue($instance);
		}
		$controllers = $instance['controller'];
		$action      = $instance['action'];
		$value       = $instance['value'];
		unset($instance);
		//执行默认的
		if (empty($instance) || (empty($controllers) && empty($action))) {
			self::_getController($default, $value);
		}
		//执行已注册的
		$url = implode('/', $controllers);
		if (!empty($action)) {
			$url .= '/'.$action;
		}
		self::_loopConfig($url, $config, $value);
		unset($config, $url);
		//自动判断
		if (empty($controllers)) {
			$controllers = array('home');
		}
		if (empty($action)) {
			$action = 'index';
		}
		self::_autoload($controllers, $action, $value);
	}
	
	/**
	 * 根据数字判断是否带值
	 * @param array $routes
	 * @return array:
	 */
	private function _getValue($routes) {
		$values = array();
		for ($i = 0, $len = count($routes); $i < $len; $i++) {
			if (is_numeric($routes[$i])) {
				$values = array_splice($routes, $i);
				break;
			}
		}
		switch (count($routes)) {
			case 0:
				$routes[] = 'home';
			case 1:
				$routes[] = 'index';
				break;
			default:
				break;
		}
		return array(
				'action'   => array_pop($routes),
				'controller' => OArray::ucFirst($routes),
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
				self::_getController($instance, array_merge($matchs, (array)$value));
			}
		}
	}
	
	/**
	 * 执行
	 * @param unknown $instance
	 * @param unknown $value
	 */
	private static function _getController($instance, $value = array()) {
		if (is_object($instance)) {
			call_user_func_array($instance, $value);
			exit();
		}
		list($controller, $action) = explode('@', $instance, 2);
		self::_runController($controller, $action, $value);
	}
	/**
	 * 执行
	 * @param unknown $controller
	 * @param unknown $action
	 * @param unknown $value
	 * @throws Error
	 */
	private static function _runController($controller, $action, $value = array()) {
		if (!class_exists($controller)) {
			throw new Error('Not find class : '.$controller);
		}
		$instance = new $controller();
		if (!method_exists($instance, $action)) {
			throw new Error($controller.' class doesn\'t find method :'.$action);
		}
		if (method_exists($instance, 'before')) {
			$instance -> before(str_replace(APP_ACTION, '', $action));
		}
		call_user_func_array(array($instance, $action), (array)$value);
		exit();
	}
	/**
	 * 加载控制器和视图
	 *
	 * @access globe
	 * @param $c string 控制器的名称
	 * @param $v string 视图所在的方法名
	 */
	private static function _autoload($controllers, $action, $values = array()) {
		if(self::call_func($controllers, $action, $values)) {
			return ;
		}
		if ($action == 'index' && count($controllers) == 1 && self::call_func(array('Home'), strtolower($controllers[0]), $values)) {
			return ;
		}
		$tem = $controllers;
		$tem[] = $action;
		if(self::call_func($tem, 'index', $values)) {
			return ;
		}
		unset($tem);
		for ($i = 0, $len = count($controllers); $i < $len; $i ++) {
			array_unshift($values, $action);
			$action = array_pop($controllers);
			if (empty($controllers)) {
				$controllers = array('home');
			}
			if (self::call_func($controllers, $action, $values)) {
				return ;
			}
		}
		array_unshift($values, $action);
		$action = 'index';
		if (self::call_func($controllers, $action, $values)) {
			return ;
		}
		throw new Error('路由不存在！');
	
	}
	
	private static function call_func($controllers, $action, $values) {
		$controller = APP_MODULE. '\\Controller\\'. implode('\\', $controllers). APP_CONTROLLER;
		if ( class_exists($controller)) {
			$controller = new $controller();
			if (method_exists($controller, $action. APP_ACTION)) {
				$controller -> before($action);
				self::$route = array(
						'controller' => $controllers,
						'action'     => $action,
						'value'      => $values
				);
				call_user_func_array( array($controller, $action. APP_ACTION), $values);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 带index。php的链接解析，格式 index.php/home/index
	 */
	private function u() {
		$url = HUrl::request_uri();
		$arr = OString::toArray($url, '.php', 2, array('', '/home/index'));
		return $this->getRoute($arr[1]);
	}
	
	/**
	 * 优雅链接解析
	 */
	private function y() {
		$url    = HUrl::request_uri();
		$url    = explode('?', $url)[0];
		return $this->getRoute($url);
	}
	
	/**
	 * 自定义正则匹配路由
	 * @return unknown
	 */
	private function p() {
		$url = HUrl::request_uri();
		preg_match($preg, $url, $result);
		return $result;
	}
	
	/**
	 * 控制台的路由
	 */
	private function cli() {
		$url = Base::$request->server('argv')[0];
		return $this->getRoute($url);
	}
	
	/**
	 * 获取路由 home/index
	 * @param string $url
	 * @return array
	 */
	private function getRoute($url = 'home/index') {
		$url    = trim($url, '/');
		$routes = explode('/', $url);
		if (!isset($routes[0]) || $routes[0] === '') {
			$routes[0] = 'home';
		}
		if (!isset($routes[1]) || $routes[1] === '') {
			$routes[1] = 'index';
		}
		return $this->getValue($routes);
	}
	
	
	
	/**
	 * 判断当前网址是否是url
	 * @param string $url
	 * @return boolean
	 */
	public static function judge($url = null) {
		$route = implode('/', self::$route['controller']);
		if ($url === $route) {
			return true;
		}
		$route .= '/'. self::$route['action'];
		if (empty($url) || $url == '/') {
			return $route == 'Home/index';
		}
		if ($url === $route) {
			return true;
		}
		$route .= '/'. implode('/', self::$route['value']);
		if ($url === $route) {
			return true;
		}
		return false;
	}
}