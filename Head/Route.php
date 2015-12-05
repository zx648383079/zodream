<?php 
namespace Zodream\Head;
/**
* 路由
* 
* @author Jason
* @time 2015-12-3
*/

use Zodream\Body\Object\Obj;
use Zodream\Body\Object\Arr;
use Zodream\Body\Config;
use Zodream\Body\Error;

defined('APP_CONTROLLER') or define('APP_CONTROLLER', Config::getInstance()->get('app.controller'));
defined('APP_ACTION')     or define('APP_ACTION', Config::getInstance()->get('app.action'));

class Route extends Obj{
	
	protected static $instance;
	/**
	 * 单例
	 */
	public static function getInstance() {
		if (is_null(static::$instance)) {
			static::$instance = new static;
		}
		return static::$instance;
	}
	
	protected $driver;
	
	protected function __construct() {
		$this->reset();
	}
	
	public function reset() {
		$config = Config::getInstance()->get('route');
		$this->driver = $config['driver'];
		unset($config['driver']);
		$this->set($config);
	}
	
	/**
	 * 获取驱动
	 * @throws Error
	 */
	public function getDriver() {
		if (empty($this->driver)) {
			throw new Error('NOT FIND ROUTE DRIVER!');
		}
		return $this->driver;
	}
	
	/**
	 * 成功执行的method
	 * @var unknown
	 */
	public static $method;
	
	/**
	 * 运行加载方法
	 */
	public static function run() {
		$instance = call_user_func(array(static::getInstance()->getDriver(), 'get'));
		if (!is_array($instance)) {
			$instance = self::_getValue($instance);
		}
		$controllers = $instance['controller'];
		$action      = $instance['action'];
		$value       = $instance['value'];
		unset($instance);
		//执行默认的
		if (empty($controllers) && empty($action)) {
			self::_getController(static::getInstance()->get('default'), $value);
		}
		//执行已注册的
		$url = implode('/', $controllers);
		if (!empty($action)) {
			$url .= '/'.$action;
		}
		self::_loopConfig($url, static::getInstance()->get(), $value);
		
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
				'controller' => Arr::ucFirst($routes),
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
			$controller = APP_MODULE. '\\Head\\'. $controller.APP_CONTROLLER;
			if (!class_exists($controller)) {
				throw new Error('NOT FIND CONTROLLER.'. $controller);
			}
		}
		$instance = new $controller();
		if (!method_exists($instance, $action)) {
			throw new Error($controller.' class doesn\'t find method :'.$action);
		}
		if (method_exists($instance, 'before')) {
			$instance -> before(str_replace(APP_ACTION, '', $action));
		}
		self::$method = $controller.'::'.$action;
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
		$controller = APP_MODULE. '\\Head\\'. implode('\\', $controllers). APP_CONTROLLER;
		if ( class_exists($controller)) {
			$instance = new $controller();
			if (method_exists($instance, $action. APP_ACTION)) {
				$instance -> before($action);
				self::$method = $controller.'::'.$action.APP_ACTION;
				call_user_func_array( array($instance, $action. APP_ACTION), $values);
				return true;
			}
		}
		return false;
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