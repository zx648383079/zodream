<?php
namespace Zodream\Domain\Routing;
/**
 * 控制器基类
 *
 * @author Jason
 * @time 2015-12-19
 */
use Zodream\Domain\Access\Auth;
use Zodream\Domain\Html\VerifyCsrfToken;
use Zodream\Domain\Response\AjaxResponse;
use Zodream\Domain\Response\BaseResponse;
use Zodream\Domain\Response\HtmlResponse;
use Zodream\Domain\Response\RedirectResponse;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\Error\Error;
use Zodream\Infrastructure\EventManager\EventManger;

abstract class BaseController extends Action {
	
	protected $action = 'index';

	protected $canCache;

	/**
	 * AUTO CSRF
	 * @var bool
	 */
	protected $canCSRFValidate;
	
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
		$this->action = $action;
		if ($this->canCSRFValidate && Request::isPost() && !VerifyCsrfToken::verify()) {
			return Error::out('BAD POST REQUEST!', __FILE__, __LINE__);
		}
		if ($this->canCSRFValidate) {
			VerifyCsrfToken::create();
		}
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
	
	private function runClassAction($action) {
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

	private function runAllAction($action, $vars = array()) {
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
		if ($this->canCache && Request::isGet() && (($cache = $this->runCache(get_called_class().$this->action.serialize($arguments))) !== false)) {
			return new HtmlResponse($cache);
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

	/**
	 * 传递数据
	 *
	 * @param string|array $key 要传的数组或关键字
	 * @param string $value  要传的值
	 * @return static
	 */
	public function send($key, $value = null) {
		Factory::view()->set($key, $value);
		return $this;
	}

	/**
	 * 加载视图
	 *
	 * @param string $name 视图的文件名
	 * @param array $data 要传的数据
	 * @return BaseResponse
	 */
	public function show($name = null, $data = array()) {
		if (is_array($name)) {
			$data = $name;
			$name = null;
		}
		if (is_null($name)) {
			$name = $this->action;
		}
		if (strpos($name, '/') !== 0) {
			$pattern = 'Service.'.APP_MODULE.'.(.+)'.APP_CONTROLLER;
			$name = preg_replace('/^'.$pattern.'$/', '$1', get_called_class()).'/'.$name;
		}
		return new HtmlResponse(Factory::view()->render($name, $data));
	}

	public function ajax($data, $type = AjaxResponse::JSON) {
		return new AjaxResponse($data, $type);
	}

	public function redirect($url, $time = 0, $message = null, $status = 200) {
		return new RedirectResponse($url, $time, $message, $status);
	}

	public function goHome() {
		return $this->redirect(Url::getRoot());
	}

	public function goBack() {
		return $this->redirect(Url::referrer());
	}

	/**
	 * 在执行之前做规则验证
	 * @param string $action 方法名
	 * @return boolean|BaseResponse
	 */
	private function beforeFilter($action) {
		$action = str_replace(APP_ACTION, '', $action);
		$rules = $this->rules();
		foreach ($rules as $key => $item) {
			if ($action === $key) {
				return $this->process($item);
			}
			if (is_integer($key) && is_array($item)) {
				$key = (array)array_shift($item);
				if (in_array($action, $key)) {
					return $this->process($item);
				}
			}
		}
		if (array_key_exists('*', $rules)) {
			return $this->process($rules['*']);
		}
		return true;
	}

	/**
	 * @param string|callable $role
	 * @return bool|RedirectResponse
	 */
	private function process($role) {
		if (is_callable($role)) {
			return call_user_func($role);
		}
		if (empty($role)) {
			return true;
		}
		if (is_string($role)) {
			$role = explode(',', $role);
		}
		foreach ((array)$role as $item) {
			if (true !== ($arg = $this->processOne($item))) {
				return $arg;
			}
		}
		return true;
	}

	private function processOne($role) {
		if ($role === '*') {
			return true;
		}
		if ($role === '?') {
			return Auth::guest() ?: $this->redirect('/');
		}
		if ($role === '@') {
			return !Auth::guest() ?: $this->redirect([Config::getValue('auth.home'), 'ReturnUrl' => Url::to()]);
		}
		if ($role === 'p' || $role === 'post') {
			return Request::isPost() ?: $this->redirect('/', 4, '您不能直接访问此页面！', '400');
		}
		if ($role === '!') {
			return $this->redirect('/', 4, '您访问的页面暂未开放！', '413');
		}
		return true;
	}

	/**
	 * 执行缓存
	 * @param $key
	 * @return bool|string
	 */
	public function runCache($key = null) {
		$update = Request::get('cache', false);
		if (!Auth::guest() && empty($update)) {
			return false;
		}
		if (empty($key)) {
			$key = get_called_class().$this->action;
		}
		if (!is_string($key)) {
			$key = serialize($key);
		}
		$key = 'views/'.md5($key);
		if (empty($update) && ($cache = Factory::cache()->get($key))) {
			return $cache;
		}
		$this->send('updateCache', true);
		EventManger::getInstance()->add('showView', function ($content) use ($key) {
			Factory::cache()->set($key, $content, 12 * 3600);
		});
		return false;
	}
}