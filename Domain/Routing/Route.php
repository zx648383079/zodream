<?php
namespace Zodream\Domain\Routing;

class Route {
	protected $_action;
	
	protected $_param;
	
	public function __construct($action, $param = null) {
		$this->_action = $action;
		$this->_param  = (array)$param;
	}
	
	public function run() {
		if (!is_string($this->_action)) {
			return $this->runCallback();
		}
		if (strstr($this->_action, '::') !== false) {
			return $this->runStatic();
		}
		if (strstr($this->_action, '@') === false) {
			return $this->runClass();
		}
		return $this->runController();
	}
	
	/**
	 * 执行注册的匿名方法 或 带 :: 的静态方法
	 * @return mixed
	 */
	protected function runCallback() {
		return call_user_func_array($this->_action, $this->_param);
	}
	
	/**
	 * 执行已注册的静态方法
	 */
	protected function runStatic() {
		if (is_callable($this->_action)) {
			return $this->runCallback();
		}
	}
	
	/**
	 * 执行class
	 */
	protected function runClass() {
		if (class_exists($this->_action)) {
			return new $this->_action($this->_param);
		}
	}
	
	/**
	 * 执行 控制器方法
	 * @throws Error
	 */
	protected function runController() {
		list($class, $action) = explode('@', $this->_action);
		if (!class_exists($class)) {
			$class = 'Service\\'.APP_MODULE.'\\'.ucfirst(strtolower($class)).APP_CONTROLLER;
			$action .= APP_ACTION;
		}
		if (!class_exists($class)) {
			throw new Error('NOT FIND PAGE!', '404');
		}
		$this->runFilter($instance = new $class, $action);
		return call_user_func_array(array($instance, $action), $this->_param);
	}
	
	/**
	 * 执行筛选
	 * @param unknown $instance
	 */
	protected function runFilter($instance, $action) {
		if (method_exists($instance, 'beforeFilter')) {
			$instance->beforeFilter(str_replace(APP_ACTION, '', $action));
		}
	}
}