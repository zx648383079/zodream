<?php
namespace Zodream\Domain\Routing;
/**
 * 控制器基类
 *
 * @author Jason
 * @time 2015-12-19
 */
use Zodream\Domain\Response\BaseResponse;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\Error\Error;
use Zodream\Infrastructure\EventManager\EventManger;
use Zodream\Infrastructure\Traits\AccessTrait;

abstract class BaseController extends Action {

	use AccessTrait;
	
	protected function actions() {
		return [];
	}

	/**
	 * 执行方法
	 * @param string $action
	 * @param array $vars
	 * @return string|BaseResponse
	 */
	public function runAction($action, array $vars = array()) {
		if (!$this->hasAction($action)) {
			return $this->redirect('/', 4, 'URI ERROR!');
		}
		if (true !== ($arg = $this->beforeFilter($action))) {
			return $arg;
		}
		if (array_key_exists($action, $this->actions())) {
			return $this->runClassAction($action);
		}
		$this->prepare();
		EventManger::getInstance()->run('runController', $vars);
		$result = $this->runAllAction($action, $vars);
		$this->finalize();
		return $result;
	}
	
	protected function runClassAction($action) {
		$class = $this->actions()[$action];
		if (is_callable($class)) {
			return call_user_func($class);
		}
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
		return call_user_func_array(array($this, $action), $arguments);
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
		return array_key_exists($action, $this->actions()) || method_exists($this, $action.APP_ACTION);
	}
}