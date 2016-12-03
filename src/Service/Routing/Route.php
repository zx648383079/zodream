<?php
namespace Zodream\Service\Routing;

/**
 * 单个路由
 * @author Jason
 */
use Zodream\Domain\Filter\DataFilter;
use Zodream\Service\Config;
use Zodream\Infrastructure\Error\Error;
use Zodream\Service\Factory;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
use Zodream\Infrastructure\Http\Request;
use Zodream\Infrastructure\Http\Response;

class Route {

    const PATTERN = '#{([\w_]+)}#i';

	protected $uri;

	protected $methods = [];

	protected $action = [];

    /**
     * VALIDATE VALUE
     * @var array
     */
    protected $rules = [];

	/**
	 * Route constructor.
	 * @param $methods
	 * @param $uri
	 * @param string|object $action
	 */
	public function __construct($methods, $uri, $action) {
		$this->uri = preg_replace(self::PATTERN, '(?$1:.*?)', $uri);
		$this->methods = (array) $methods;
		$this->action = $this->parseAction($action);
		if (!array_key_exists('param', $this->action)) {
			$this->action['param'] = [];
		}
		if (in_array('GET', $this->methods) && ! in_array('HEAD', $this->methods)) {
			$this->methods[] = 'HEAD';
		}
	}

	protected function parseAction($action) {
		if (is_string($action)) {
			$action = trim($action, '/\\');
		}
		// If no action is passed in right away, we assume the user will make use of
		// fluent routing. In that case, we set a default closure, to be executed
		// if the user never explicitly sets an action to handle the given uri.
		if (empty($action)) {
			return ['uses' => Config::getValue('route.default', 'home/index')];
		}


		// If the action is already a Closure instance, we will just set that instance
		// as the "uses" property, because there is nothing else we need to do when
		// it is available. Otherwise we will need to find it in the action list.
		if (is_callable($action)) {
			return ['uses' => $action];
		}

		if (!is_array($action)) {
			return ['uses' => $action];
		}

		// If no "uses" property has been set, we will dig through the array to find a
		// Closure instance within this list. We will set the first Closure we come
		// across into the "uses" property that will get fired off by this route.
		if (!isset($action['uses'])) {
			$action['uses'] = $this->findCallable($action);
		}
		return $action;
	}

	protected function findCallable(array $action) {
		return ArrayExpand::first($action, function ($key, $value) {
			return is_callable($value) && is_numeric($key);
		});
	}
	
	public function getMethods() {
		return $this->methods;
	}
	
	public function getUri() {
		return $this->uri;
	}

    /**
     * CAN RUN ROUTE
     * @param string $url
     * @return bool
     */
    public function canRun($url) {
        if (preg_match('#'.$this->uri.'#', $url, $match, PREG_OFFSET_CAPTURE)) {
            Request::get(true)->set($match);
            return true;
        }
        return false;
    }

    public function filter($key, $pattern) {
        $this->rules[$key] = $pattern;
        return $this;
    }

	/**
	 * 执行路由
	 * @return Response
	 */
	public function run() {
		return $this->parseResponse($this->runAction());
	}

	protected function runFilter() {
	    if (!DataFilter::validate(Request::get(), $this->rules)) {
	        throw new \InvalidArgumentException('URL ERROR');
        }
    }
	
	protected function runAction() {
	    $this->runFilter();
		$action = $this->action['uses'];
		// 排除一个的方法
		if (is_callable($action) && (!is_string($action) || strpos($action, '\\') > 0)) {
			return call_user_func($action);
		}
		if (strpos($action, '@') === false) {
			return $this->runClassWithConstruct($action);
		}
		return $this->runClassAndAction($action);
	}

	protected function runClassWithConstruct($action) {
		if (class_exists($action)) {
			return new $action;
		}
		return $this->runController($action);
	}

	/**
	 * @param $response
	 * @return Response
	 */
	protected function parseResponse($response) {
		if ($response instanceof Response) {
			return $response;
		}
		if (empty($response) || is_bool($response)) {
            return Factory::response();
        }
		return new Response($response);
	}

	/**
	 * 执行动态方法
	 * @param $arg
	 * @return mixed
	 */
	protected function runClassAndAction($arg) {
		list($class, $action) = explode('@', $arg);
		if (!class_exists($class)) {
			return $this->runController($class, $action);
		}
		$reflectionClass = new \ReflectionClass( $class );
		$method = $reflectionClass->getMethod($action);

		$parameters = $method->getParameters();
		$arguments = array();
		foreach ($parameters as $param) {
			$arguments[] = Request::get($param->getName());
		}
		return call_user_func_array(array(new $class, $action), $arguments);
	}


	/**
	 * 执行 控制器方法
	 * @param string $class
	 * @param string $action
	 * @return mixed
	 */
	protected function runController($class = null, $action = null) {
		$classes = explode('\\', str_replace('/', '\\', $class));
		if (empty($action)) {
			list($class, $action) = $this->getController($classes);
		} else {
			$class = $this->getClass($classes);
		}
		if (!class_exists($class)) {
			Error::out($class.' CLASS NOT EXISTS!', __FILE__, __LINE__);
		}
		/** @var BaseController $instance */
		$instance = new $class;
		$instance->init();
		return call_user_func(array($instance, 'runAction'), $action, $this->action['param']);
	}

	/**
	 * 获取控制
	 * @param array $args
	 * @return array|bool
	 */
	protected function getController(array $args) {
		if (empty($args)) {
			$args = ['home'];
		}
		$class = $this->getClass($args);
		if (class_exists($class)) {
			return [$class, 'index'];
		}
		$action = array_pop($args);
		if (empty($args)) {
			$args = ['home'];
		}
		return [$this->getClass($args), $action];
	}

	protected function getClass(array $args) {
		$args = array_map('ucfirst', $args);
		return 'Service\\'.APP_MODULE.'\\'.implode('\\', $args).APP_CONTROLLER;
	}
}