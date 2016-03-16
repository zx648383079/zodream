<?php
namespace Zodream\Domain\Routing;
/**
 * 控制器基类
 *
 * @author Jason
 * @time 2015-12-19
 */
use Zodream\Infrastructure\Request;
use Zodream\Domain\Authentication\Verify;
use Zodream\Infrastructure\Error;
use Zodream\Infrastructure\EventManager\EventManger;

abstract class BaseController {
	
	public function init() { }
	
	/**
	 * 在执行之前做规则验证
	 * @param string $action 方法名
	 * @return boolean
	 */
	protected function beforeFilter($action) {
		$action = str_replace(APP_ACTION, '', $action);
		if (isset($this->rules)) {
			$role = isset($this->rules['*']) ? $this->rules['*'] : '';
			$role = isset($this->rules[$action]) ? $this->rules[$action] : $role;
			return Verify::make($role);
		}
		return TRUE;
	}
	
	public function prepare() {  }
	
	public function finalize() {  }
	/**
	 * 执行方法
	 * @param string $action
	 * @param array $parameters
	 */
	public function runAction($action, array $vars = array()) {
		if (!$this->canRunAction($action)) {
			Error::out($action .' ACTION CANOT RUN!', __FILE__, __LINE__);
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
		EventManger::getInstance()->run('runController', $arguments);
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