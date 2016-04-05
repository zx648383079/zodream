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

	protected $rules = array();

	/**
	 * 此方法主要是为了继承并附加规则
	 * @return array
	 */
	protected function rules() {
		return array();
	}

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
	 * @param array $vars
	 * @return mixed
	 */
	public function runAction($action, array $vars = array()) {
		//合并过滤规则
		$this->rules = array_merge($this->rules, $this->rules());

		if (!$this->canRunAction($action)) {
			Error::out($action .' ACTION CANNOT RUN!', __FILE__, __LINE__);
		}
		$this->prepare();
		EventManger::getInstance()->run('runController', $vars);
		$result = Request::isPost() ?
			$this->runPostAction($action, $vars) : $this->runAllAction($action, $vars);
		$this->finalize();
		return $result;
	}

	protected function runPostAction($action, $vars = array()) {
		if (!$this->hasAction($action.'Post')) {
			return $this->runAllAction($action, $vars);
		}
		$action .= 'Post'.APP_ACTION;
		return $this->$action(Request::post(), $vars);
	}

	protected function runAllAction($action, $vars = array()) {
		$reflectionObject = new \ReflectionObject( $this );
		$action .= APP_ACTION;
		$method = $reflectionObject->getMethod($action);

		$parameters = $method->getParameters();
		$arguments = array();
		foreach ($parameters as $param) {
			if ( isset( $vars[ $param->getName() ] ) ) {
				$arguments[] = $vars[ $param->getName() ];
			} else {
				$arguments[] = Request::get($param->getName());
			}
		}
		return call_user_func_array( array($this, $action) , $arguments );
	}
	/**
	 * 加载其他控制器的方法
	 * @param static|string $controller
	 * @param string $actionName
	 * @param array $parameters
	 */
	public function forward($controller, $actionName = 'index' , $parameters = array()) {
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