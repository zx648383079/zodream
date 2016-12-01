<?php
namespace Zodream\Domain\Controller;
/**
 * 控制器基类
 *
 * @author Jason
 * @time 2015-12-19
 */
use Zodream\Domain\Access\Auth;
use Zodream\Domain\Html\VerifyCsrfToken;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\Error\Error;
use Zodream\Infrastructure\Event\EventManger;
use Zodream\Infrastructure\Response;
use Zodream\Infrastructure\Url\Url;

abstract class BaseController extends Action {
	
	protected $action = 'index';
	
	protected function actions() {
		return [];
	}

	/**
	 * 此方法主要是为了继承并附加规则
	 * @return array
	 */
	protected function rules() {
		return [];
	}

	/**
	 * 执行方法
	 * @param string $action
	 * @param array $vars
	 * @return string|BaseResponse
	 */
	public function runAction($action, array $vars = array()) {
        Factory::timer()->record('controllerStart');
		$this->action = $action;
		if (!$this->hasAction($action)) {
			throw new \HttpUrlException('URI ERROR!');
		}
		if (true !==
            ($arg = $this->beforeFilter($action))) {
			return $arg;
		}
		if (array_key_exists($action, $this->actions())) {
			return $this->runClassAction($action);
		}
		$this->prepare();
		EventManger::getInstance()->run('runController', $vars);
		$result = $this->runActionMethod($action.APP_ACTION, $vars);
		$this->finalize();
        Factory::timer()->record('controllerEnd');
		return $result;
	}
	
	private function runClassAction($action) {
		$class = $this->actions()[$action];
		if (is_callable($class)) {
			return call_user_func($class);
		}
		if (!class_exists($class)) {
			throw new \HttpUrlException($action. ' CANNOT RUN CLASS!');
		}
		$instance = new $class;
		if (!$instance instanceof Action) {
			throw new \HttpUrlException($action. ' IS NOT ACTION!');
		}
		$instance->init();
		$instance->prepare();
		$result = $instance->run();
		$instance->finalize();
		return $result;
	}

    /**
     * RUN THIS METHOD ACTION
     * @param string $action
     * @param array $vars
     * @return mixed
     */
	protected function runActionMethod($action, $vars = array()) {
		return call_user_func_array(
		    array($this, $action),
            $this->getActionArguments($action, $vars)
        );
	}

    /**
     * GET ACTION NEED ARGUMENTS
     * @param string $action
     * @param array $vars
     * @return array
     * @throws \HttpUrlException
     */
	protected function getActionArguments($action, $vars = array()) {
        $reflectionObject = new \ReflectionObject($this);
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
            throw new \HttpUrlException($action.' ACTION`S '.$name, ' DOES NOT HAVE VALUE!');
        }
        return $arguments;
    }

	/**
	 * 加载其他控制器的方法
	 * @param static|string $controller
	 * @param string $actionName
	 * @param array $parameters
	 */
	public function forward(
	    $controller,
        $actionName = 'index' ,
        $parameters = array()
    ) {
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
		return array_key_exists($action, $this->actions())
        || method_exists($this, $action.APP_ACTION);
	}



    /**
     * 验证方法是否合法
     * @param $action
     * @return boolean|Response
     */
    abstract protected function beforeFilter($action);

}