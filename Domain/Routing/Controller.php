<?php
namespace Zodream\Domain\Routing;
/**
 * 控制器基类
 *
 * @author Jason
 * @time 2015-12-19
 */
use Zodream\Infrastructure\Loader;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Language;
use Zodream\Infrastructure\Traits\LoaderTrait;
use Zodream\Infrastructure\Traits\ValidataTrait;
use Zodream\Infrastructure\Error;
use Zodream\Domain\Validater\ControllerValidate;
use Zodream\Infrastructure\Traits\ViewTrait;
use Zodream\Infrastructure\Request;

abstract class Controller {
	
	use LoaderTrait, ValidataTrait, ViewTrait;
	
	function __construct($loader = null) {
		$this->loader = $loader instanceof Loader ? $loader : new Loader();
	}
	
	public function init() { }
	
	/**
	 * 在执行之前做规则验证
	 * @param string $action 方法名
	 * @return boolean
	 */
	protected function beforeFilter($action) {
		if (isset($this->rules)) {
			$role = isset($this->rules['*']) ? $this->rules['*'] : '';
			$role = isset($this->rules[$func]) ? $this->rules[$func] : $role;
			return ControllerValidate::make($role);
		}
		return TRUE;
	}
	
	public function prepare() {  }
	
	public function finalize() {  }
	/**
	 * 执行方法
	 * @param unknown $action
	 * @param array $parameters
	 * @throws Error
	 */
	public function runAction($action, array $vars = array()) {
		if (!$this->canRunAction($action)) {
			throw new Error($action .' ACTION CANOT RUN!');
		}
		$this->prepare();
		
		$reflectionObject = new \ReflectionObject( $this );
		$action .= APP_ACTION;
		$method = $reflectionObject->getMethod($action);
		
		$parameters = $method->getParameters();
		$arguments = array();
		foreach ($parameters as $param) {
			if ( isset( $vars[ $param->getName() ] ) ) {
				$arguments[] = $vars[ $param->getName() ];
			} else {
				$arguments[] = Request::getInstance()->get($param->getName());
			}
		}
		$ret = call_user_func_array( array($this, $action) , $arguments );
		
		$this->finalize();
		return $ret;
	}
	/**
	 * 加载其他控制器的方法
	 * @param string $controller
	 * @param string $actionName
	 * @param array $parameters
	 */
	public function forward($controller, $actionName = 'index' , $parameters = array())
	{
		if (is_string($controller)) {
			$controller = new $controller;
		}
		return $controller->runAction($actionName, $parameters);
	}
	
	/**
	 * 判断是否存在方法
	 * @param string $action
	 * @return boolean
	 */
	public function hasAction($action) {
		return method_exists($this, $action.APP_ACTION);
	}
	
	/**
	 * 判断是否能执行方法
	 * @param string $action
	 * @return boolean
	 */
	public function canRunAction($action) {
		return $this->hasAction($action) && $this->beforeFilter($action);
	}
}