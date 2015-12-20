<?php
namespace Zodream\Domain\Routing;

use Zodream\Infrastructure\Error;

class Route {
	protected $_action;
	
	protected $_param;
	
	protected $_class = null;
	
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
	
	public function getClass() {
		return $this->_class;
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
		if (empty($class) || empty($action)) {
			throw new Error('CLASS OR ACTION IS EMPTY!');
		}
		if (class_exists($class)) {
			return $this->runControllerWithConfirm($class, $action);
		}
		$this->_class = $class = str_replace(APP_CONTROLLER, '', $class);
		$class .= APP_CONTROLLER;
		if (strstr('Service\\', $class) === false) {
			$class = 'Service\\'.APP_MODULE.'\\' .ucfirst($class);
		}
		if (!class_exists($class)) {
			throw new Error('NOT FIND CLASS!'. $class);
		}
		$action = str_replace(APP_ACTION, '', $action);
		$this->_class .= '@'. $action;
		$action = strtolower($action).APP_ACTION;
		$this->runControllerWithConfirm($class, $action);
	}
	
	/**
	 * 执行已确认class存在的
	 * @param unknown $class
	 * @param unknown $action
	 * @return mixed
	 */
	protected function runControllerWithConfirm($class, $action) {
		$this->runFilter($instance = new $class, $action);
		if (method_exists($instance, $action)) {
			return call_user_func_array(array($instance, $action), $this->_param);
		}
		throw Error('Method Not Exists!');
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