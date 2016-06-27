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
use Zodream\Infrastructure\Error\Error;
use Zodream\Infrastructure\EventManager\EventManger;

abstract class BaseController extends Action {

	protected $rules = array();

	/**
	 * 此方法主要是为了继承并附加规则
	 * @return array
	 */
	protected function rules() {
		return array();
	}

	protected function actions() {
		return array();
	}

	
	
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

	/**
	 * 执行方法
	 * @param string $action
	 * @param array $vars
	 * @return mixed
	 */
	public function runAction($action, array $vars = array()) {
		//合并过滤规则
		$this->rules = array_merge($this->rules, $this->rules());

		$result = $this->runClassAction($action);
		if ($result !== false) {
			return $result;
		}
		
		if (!$this->canRunAction($action)) {
			Error::out($action .' ACTION CANNOT RUN!', __FILE__, __LINE__);
		}
		
		$this->prepare();
		EventManger::getInstance()->run('runController', $vars);
		if (Request::isPost()) {
			$this->runPostAction($action, $vars);
		}
		$result = $this->runAllAction($action, $vars);
		$this->finalize();
		return $result;
	}
	
	protected function runClassAction($action) {
		$actions = $this->actions();
		if (!array_key_exists($action, $actions)) {
			return false;
		}
		if (!$this->beforeFilter($action)) {
			Error::out($action. ' CANNOT RUN!', __FILE__, __LINE__);
		}
		$class = $actions[$action];
		if (!class_exists($class)) {
			Error::out($action. ' CANNOT RUN CLASS!', __FILE__, __LINE__);
		}
		$instance = new $class;
		if (!$instance instanceof Action) {
			Error::out($action. ' IS NOT ACTION!', __FILE__, __LINE__);
		}
		$instance->init();
		$instance->prepare();
		$result = $instance->run();
		$instance->finalize();
		return $result;
	}

	/**
	 * @param string $action
	 * @param array $vars
	 */
	protected function runPostAction($action, $vars = array()) {
		if (method_exists($this, $action.'Post')) {
			$action .= 'Post';
			$this->$action(Request::post(true), $vars);
		}
	}

	protected function runAllAction($action, $vars = array()) {
		$reflectionObject = new \ReflectionObject( $this );
		$action .= APP_ACTION;
		$method = $reflectionObject->getMethod($action);

		$parameters = $method->getParameters();
		$arguments = array();
		foreach ($parameters as $param) {
			$name = $param->getName();
			if (array_key_exists($name, $vars)) {
				$arguments[] = $vars[$name];
				continue;
			}
			$value = Request::get($name);
			if (!is_null($value)){
				$arguments[] = Request::get($name);
				continue;
			}
			if ($param->isDefaultValueAvailable()) {
				$arguments[] = $param->getDefaultValue();
				continue;
			}
			Error::out($action.' ACTION`S '.$name, ' DOES NOT HAVE VALUE!', __FILE__, __LINE__);
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