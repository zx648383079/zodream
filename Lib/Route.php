<?php 
namespace App\Lib;
/*****************************************************
* 路由
*
*
*
********************************************************/
use App;
use App\Lib\Helper\HUrl;
use App\Lib\Object\OString;
use App\Lib\Object\OArray;

defined("APP_URL") or define('APP_URL', Base::config('app.host'));

class Route {
	/**
	 * 加载控制器和视图
	 *
	 * @access globe
	 * @param $c string 控制器的名称
	 * @param $v string 视图所在的方法名
	 */
	public static function load($arg = 'app') {
		$routes = self::get();
		$name = ucfirst(strtolower($arg)). '\\Controller\\'. implode('\\', $routes['controller']). 'Controller';
		$view = $routes['function'];
		if ( class_exists($name)) {
		    $controller = new $name();
		    $controller -> before($view);
		    $view .= 'Action';
		    if (method_exists($controller, $view)) {
		        call_user_func_array( array($controller, $view), $routes['value']);
		    } else {
		        App::error(0, $view, __FILE__, __LINE__);
		    }
		} else {
		    App::error(0, $name.$view, __FILE__ ,__LINE__);
		}
		
	}
	
	/**
	 * 分类获取路由
	 * @return \App\Lib\array|multitype:\App\Lib\array |\App\Lib\Ambigous|\App\Lib\unknown
	 */
	private static function get() {
		$key = App::config('app.url');
		if (empty($key)) {
			$key = 0;
		}
		$url = new Route();
		if (!empty(App::$request) && App::$request->isCli()) {
			return $url->cli();	
		}
		
		switch ($key) {
			case 0:
				return $url -> c();
				break;
			case 1:
				return $url -> r();
				break;
			case 2:
				return $url -> u();
				break;
			case 3:
				return $url -> s();
				break;
			case 4:
				return $url -> y();
				break;
			case 5:
				return $url -> p();
				break;
			case 6:
			    return $url -> d();
			    break;
			default:
				return $url -> c();
				break;
		}
	} 
	
	/**
	 * 根据配置文件中自定义的路由解析
	 */
	private function d() {
		$url    = HUrl::request_uri();
		$url    = explode('?', $url)[0];
		$url    = rtrim($url, '.html');
		$url    = trim($url, '/');
		$routes = App::config('route');
		foreach ($routes as $key => $value) {
		    $pattern = str_replace(':num', '[0-9]+', $key);
		    $pattern = str_replace(':any', '[^/]+', $pattern);
		    $matchs  = array();
		    preg_match($pattern, $url, $matchs);
		    if(count($matchs) > 0) {
		        $route = $value;
		        foreach ($matchs as $k => $val) {
		          $route = str_replace('$'.$k, $val, $route);
		        }
		        return $this->getRoute($route);
		    }
		}
		return $this->getRoute($url);
	}
	
	/**
	 * 获取c、v的参数解析   ?c=home&v=index
	 * @return multitype:array NULL
	 */
	private function c() {
		$values = explode('/', App::$request->get('v' , 'index'));
		$routes = array(
			'controller' => OArray::ucFirst(explode('/', App::$request->get('c' , 'home'))),
			'function'   => array_shift($values),
			'value'      => $values
		);
		return $routes;
	}
	
	/**
	 * 获取r的参数解析     格式?r=home/index
	 */
	private function r() {
		return $this->getRoute(App::$request->get('r', 'home/index'));
	}
	
	/**
	 * 带index。php的链接解析，格式 index.php/home/index
	 */
	private function u() {
		$url    = HUrl::request_uri();
		$arr    = OString::toArray($url, '.php', 2, array('', '/home/index'));
		return $this->getRoute($arr[1]);
	}
	
	/**
	 * 优雅链接解析
	 */
	private function y() {
	    $url    = HUrl::request_uri();
	    $url    = explode('?', $url)[0];
		return $this->getRoute(rtrim($url, '.html'));
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
	 * 短链接匹配
	 * 
	 * @return Ambigous <array, string>
	 */
	private function s() {
		$key = App::$request->get('s');
		if ($key === null) {
			$url = HUrl::request_uri();
			$ar  = explode('/', $url, 2);
			$ar  = explode('?', $ar[1], 2);
			$key = $ar[0];
			$key = $key == '' ? '*' : $key;
		}
		if (strlen($key) < 4) {
			$short = App::config('short.'.$key);
			$arr   = OString::toArray($short , '.', 2, array('home', 'index')); 
		} else {
			
		}
		
		return $arr;
	}
	
	/**
	 * 控制台的路由
	 */
	private function cli() {
		$url = App::$request->server('argv')[0];
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
	 * 根据数字判断是否带值
	 * @param array $routes
	 * @return array:
	 */
	private function getValue($routes) {
	    $values = array();
	    for ($i = 0, $len = count($routes); $i < $len; $i++) {
	        if (is_numeric($routes[$i])) {
	            $values = array_splice($routes, $i);
	            break;
	        }
	    }
	    return array(
		    'function'   => array_pop($routes),
		    'controller' => OArray::ucFirst($routes),
	        'value'      => $values
		);
	}
}