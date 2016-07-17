<?php 
namespace Zodream\Domain\Routing;
/**
* 路由
* 
* @author Jason
*/
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\DomainObject\ResponseObject;
use Zodream\Infrastructure\DomainObject\RouteObject;
use Zodream\Infrastructure\EventManager\EventManger;
use Zodream\Infrastructure\Request;

defined('APP_CONTROLLER') or define('APP_CONTROLLER', Config::getInstance()->get('app.controller'));
defined('APP_ACTION')     or define('APP_ACTION', Config::getInstance()->get('app.action'));
defined('APP_MODEL')      or define('APP_MODEL', Config::getInstance()->get('app.model'));

class BaseRouter {
	

	/**
	 * @var Route[]
	 */
	protected $routes = [];
	
	public function getRoute() {
		return $this->route;
	}


	public function __construct() {
		$this->load();
	}
	
	protected function load() {
		$configs = Config::getValue('route', []);
		$file = array_key_exists('file', $configs) ? $configs['file'] : null;
		unset($configs['file'], $configs['driver']);
		foreach ($configs as $key => $item) {
			if (is_integer($key)) {
				call_user_func_array([$this, 'addRoute'], $item);
				continue;
			}
			$this->get($key, $item);
		}
		if (is_file($file)) {
			include $file;
		}
	}

	/**
	 * 路由加载及运行方法
	 * @return ResponseObject
	 */
	public function run() {
		EventManger::getInstance()->run('getRoute');
		
		return self::getRoute()->run();
	}

	/**
	 * 根据网址判断
	 * @param string $uri
	 * @return Route
	 */
	protected function runByUri($uri) {
		$method = Request::method();
		if (isset($this->routes[$method][$uri])) {
			return $this->routes[$method][$uri];
		}
		if (array_key_exists($method, $this->routes)) {
			foreach ($this->routes[$method] as $key => $item) {
				$pattern = str_replace(':num', '[0-9]+', $key);
				$pattern = str_replace(':any', '[^/]+', $pattern);
				$pattern = str_replace('/', '\\/', $pattern);
				if (preg_match('/'.$pattern.'/i', $uri, $match)) {
					return $item;
				}
			}
		}
		return new Route('GET', $uri, $uri);
	}

	/**
	 * 手动注册路由
	 * @param $method
	 * @param $uri
	 * @param $action
	 * @return Route
	 */
	public function addRoute($method, $uri, $action) {
		$route = new Route($method, $uri, $action);
		foreach ($route->getMethods() as $item) {
			$this->routes[$item][$uri] = $route;
		}
		return $route;
	}

	public function get($uri, $action = null) {
		return $this->addRoute(['GET', 'HEAD'], $uri, $action);
	}

	/**
	 * Register a new POST route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string|null  $action
	 * @return Route
	 */
	public function post($uri, $action = null) {
		return $this->addRoute('POST', $uri, $action);
	}

	/**
	 * Register a new PUT route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string|null  $action
	 * @return Route
	 */
	public function put($uri, $action = null) {
		return $this->addRoute('PUT', $uri, $action);
	}

	/**
	 * Register a new PATCH route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string|null  $action
	 * @return Route
	 */
	public function patch($uri, $action = null) {
		return $this->addRoute('PATCH', $uri, $action);
	}

	/**
	 * Register a new DELETE route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string|null  $action
	 * @return Route
	 */
	public function delete($uri, $action = null) {
		return $this->addRoute('DELETE', $uri, $action);
	}

	/**
	 * Register a new OPTIONS route with the router.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string|null  $action
	 * @return Route
	 */
	public function options($uri, $action = null) {
		return $this->addRoute('OPTIONS', $uri, $action);
	}

	/**
	 * Register a new route responding to all verbs.
	 *
	 * @param  string  $uri
	 * @param  \Closure|array|string|null  $action
	 * @return Route
	 */
	public function any($uri, $action = null) {
		$verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'];
		return $this->addRoute($verbs, $uri, $action);
	}

	/**
	 * Register a new route with the given verbs.
	 *
	 * @param  array|string  $methods
	 * @param  string  $uri
	 * @param  \Closure|array|string|null  $action
	 * @return Route
	 */
	public function match($methods, $uri, $action = null) {
		return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
	}
	
}