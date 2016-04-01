<?php 
namespace Zodream\Domain\Response;
/**
* 响应
* 
* @author Jason
* @time 2015-12-19
*/
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Error;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Traits\SingletonPattern;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
use Zodream\Domain\Html\Script;
use Zodream\Domain\Routing\UrlGenerator;
use Zodream\Domain\Routing\Router;
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\Traits\ConditionTrait;
use Zodream\Infrastructure\EventManager\EventManger;

class View extends MagicObject {
	use SingletonPattern,ConditionTrait;
	
	protected $assetDir = 'assets/';

	//APP_DIR . '/UserInterface/' . APP_MODULE
	protected $viewDir;

	protected $suffix = '.php';

	protected function __construct() {
		$config = Config::getInstance()->get('view');
		$this->setView($config['directory']);
		$this->setSuffix($config['suffix']);
	}

	/**
	 * 设置后缀
	 * @param string $arg
	 */
	public function setSuffix($arg) {
		$this->suffix = '.'.ltrim($arg, '.');
	}

	/**
	 * 获取后缀
	 * @return string
	 */
	public function getSuffix() {
		return $this->suffix;
	}

	/**
	 * 设置视图的路径
	 * @param string $dir
	 */
	public function setView($dir) {
		$this->viewDir = $dir;
	}

	/**
	 * 获取视图的路径
	 * @return string
	 */
	public function getView($file = null) {
		return StringExpand::getFile($this->viewDir, str_replace('.', '/', $file));
	}

	/**
	 * 设置资源路径
	 * @param string $arg
	 */
	public function setAsset($arg) {
		$this->assetDir = $arg.'/';
	}
	/**
	 * 获取资源路径
	 */
	public function getAsset($file = null) {
		return StringExpand::getFile($this->assetDir, $file);
	}
	
	/**
	 * 在视图中包含其他视图的方法
	 * @param string|array $names 视图文件名
	 * @param string|array $param 传给视图的内容
	 * @param string $replace 是否替换
	 */
	public function extend($names, $param = null, $replace = TRUE) {
		if (!$replace) {
			$param = array_merge((array)$this->get('_extra'), (array)$param);
		}
		$this->set('_extra', $param);
		foreach (ArrayExpand::toFile((array)$names, '.') as $value) {
			$file = $this->getView($value).$this->getSuffix();
			if (file_exists($file)) {
				include($file);
			} else {
				Error::out($file.' is not excite', __FILE__, __LINE__);
			}
		}
	}
	
	/**
	 * 输出脚本
	 */
	public function jcs() {
		$args   = func_get_args();
		$args[] = $this->get('_extra', array());
		Script::make(ArrayExpand::sort($args), $this->getAsset());
	}
	
	/**
	 * 输出资源url
	 * @param string $file
	 * @param string $isView
	 */
	public function asset($file) {
		echo UrlGenerator::toAsset($this->getAsset($file));
	}
	
	public function url($url = null, $extra = null) {
		echo UrlGenerator::to($url, $extra);
	}
	
	public function hasUrl($search = null) {
		return UrlGenerator::hasUri($search);
	}
    
    public function call($func) {
       $args = array();
       if (func_get_args() > 1) {
           $args = array_slice(func_get_args(), 1);
       }
       echo call_user_func_array($func, $args);
    }
	
	/**
	 * 直接输出
	 * @param string $key
	 * @param string $default
	 */
	public function ech($key, $default = null) {
		echo ArrayExpand::tostring($this->get($key, $default));
	}
	
	/**
	 * 加载视图
	 *
	 * @param string|array $name 视图的文件名 如果是array|null 将使用 $method引导视图 
	 * @param array|null $data 要传的数据 如果$name 为array 则$data = $name
	 */
	public function show($name = null, $data = null) {
		if (is_null($name)) {
			$this->showWithRoute();
		}
		if (is_array($name)) {
			$this->set($name);
			$this->showWithRoute();
		}
		if (is_object($name)) {
			$this->showObject($name);
		}
		if (!is_null($data)) {
			$this->set($data);
		}
		if (substr($name, 0, 1) === '@') {
			ResponseResult::make(substr($name, 1));
		}
		$this->showWithFile($name);
	}
	
	/**
	 * 如果传的是匿名函数  参数问题未解决
	 * @param \Closure $func
	 */
	protected  function showObject($func) {
		ob_start();
		$data = $func();
		$content = ob_get_contents();
		ob_end_clean();
		if (empty($data)) {
			ResponseResult::make($content);
		}
		ResponseResult::make($data);
	}
	
	/**
	 * 根据路由判断
	 */
	protected function showWithRoute() {
		list($class, $action) = Router::getClassAndAction();
		$name = str_replace('\\', '.', $class).'.'.$action;
		$this->showWithFile($name);
	}
	
	/**
	 * 根据路径判断
	 * @param string $file 路径
	 * @param integer $status 状态码
	 */
	public function showWithFile($file, $status = 200) {
		ob_start();
		include($this->getView($file).$this->getSuffix());
		$content = ob_get_contents();
		ob_end_clean();
		EventManger::getInstance()->run('showView', $content);
		ResponseResult::make($content, 'html', $status);
	}
}