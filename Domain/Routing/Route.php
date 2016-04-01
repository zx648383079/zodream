<?php
namespace Zodream\Domain\Routing;

/**
 * 单个路由
 * @author Jason
 */
use Zodream\Infrastructure\Request;
use Zodream\Domain\Response\ResponseResult;

class Route {
	protected $_action;
	protected $_param;
	protected $_isController;
	protected $_class = array();
	
	public function __construct($action, $param = null, $isController = TRUE) {
		$this->_action = $action;
		$this->_param  = (array)$param;
		$this->_isController = $isController;
	}
	
	public function run() {
		if (trim($this->_action) === '@') {
			return false;
		}
		if ($this->_isController) {
			return $this->runController();
		}
		if (!is_string($this->_action)) {
			return $this->runCallback();
		}
		if (strstr($this->_action, '::') !== false) {
			return $this->runStatic();
		}
		if (strstr($this->_action, '@') === false) {
			return $this->runClassWithConstruct();
		}
		return $this->runClassAndAction();
	}

	/** 获取当前的 class 和 action
	 * @return array|null (class, action)
	 */
	public function getClassAndAction() {
		if (!empty($this->_class)) {
			return $this->_class;
		}
		return null;
	}
	
	/**
	 * 执行注册的匿名方法 或 带 :: 的静态方法
	 * @return mixed
	 */
	protected function runCallback() {
		return call_user_func_array($this->_action, array_values($this->_param));
	}
	
	/**
	 * 执行已注册的静态方法
	 */
	protected function runStatic() {
		if (is_callable($this->_action)) {
			return $this->runCallback();
		}
		return null;
	}
	
	/**
	 * 执行class
	 */
	protected function runClassWithConstruct() {
		if (class_exists($this->_action)) {
			return new $this->_action($this->_param);
		}
		return null;
	}
	
	/**
	 * 执行动态方法
	 */
	protected function runClassAndAction() {
		list($class, $action) = explode('@', $this->_action);
		if (!class_exists($class)) {
			return $this->runController($class, $action);
		}
		$reflectionClass = new \ReflectionClass( $class );
		$method = $reflectionClass->getMethod($action);
		
		$parameters = $method->getParameters();
		$arguments = array();
		foreach ($parameters as $param) {
			if ( isset( $vars[ $param->getName() ] ) ) {
				$arguments[] = $vars[ $param->getName() ];
			} else {
				$arguments[] = Request::getInstance()->get($param->getName());
			}
		}
		return call_user_func_array(array(new $class, $action), $arguments);
	}
	
	
	/**
	 * 执行 控制器方法
	 */
	protected function runController($class = null, $action = null) {
		if (empty($class)) {
			list($class, $action) = explode('@', $this->_action);
		}
		$classes = explode('\\', $class);
		list($class, $action) = $this->getController($classes, $action);
		$this->_class[1] = $action;
		$instance        = new $class;
		$instance->init();
		return call_user_func(array($instance, 'runAction'), $action, $this->_param);
	}
	
	/**
	 * 获取控制
	 * @param array $args
	 * @param string $action
	 */
	protected function getController(array $args, $action) {
		$class = $this->existController($args);
		if ($class !== false) {
			return array($class, $action);
		}

		if (count($args) == 1 && 'index' == $action) {
			$this->_class[0] = 'Home';
			return array('Service\\'.APP_MODULE.'\\Home'.APP_CONTROLLER, strtolower($args[0]));
		}

		$args[] = $action;
		$class = $this->existController($args);
		if ($class !== false) {
			return array($class, 'index');
		}
		ResponseResult::sendError('CLASS IS NOT FIND!');
		return false;
	}
	
	protected function existController(array $args) {
		$args = array_map('ucfirst', $args);
		$method = implode('\\', $args);
		$class = 'Service\\'.APP_MODULE.'\\'.$method.APP_CONTROLLER;
		if (class_exists($class)) {
			$this->_class[0] = $method;
			return $class;
		}
		return false;
	}
}