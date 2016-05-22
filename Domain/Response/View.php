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
use Zodream\Infrastructure\ObjectExpand\TimeExpand;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\Traits\SingletonPattern;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
use Zodream\Domain\Html\Script;
use Zodream\Domain\Routing\Url;
use Zodream\Domain\Routing\Router;
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\Traits\ConditionTrait;
use Zodream\Infrastructure\EventManager\EventManger;
defined('APP_ROOT') or define('APP_ROOT', Request::server('DOCUMENT_ROOT'));

class View extends MagicObject {
	use SingletonPattern, ConditionTrait;
	
	protected $type = 'html';
	
	protected $assetDir = 'assets/';

	//APP_DIR . '/UserInterface/' . APP_MODULE
	protected $viewDir;

	protected $suffix = '.php';

	/**
	 * 公共部件
	 * @var string
	 */
	protected $layout = null;

	protected function __construct() {
		$config = Config::getInstance()->get('view');
		$this->setView($config['directory']);
		$this->setSuffix($config['suffix']);
	}

	public function setLayout($name) {
		$this->layout = $name;
	}

	/**
	 * 设置后缀
	 * @param string $arg
	 */
	public function setSuffix($arg) {
		$this->suffix = '.'.ltrim($arg, '.');
	}

	/**
	 * 设置类型
	 * @param array|string $type
	 */
	public function setType($type) {
		if (empty($type)) {
			return;
		}
		$this->type = $type;
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
	 * @param string $file
	 * @return string
	 */
	public function getView($file) {
		return StringExpand::getFile(
			$this->viewDir, 
			str_replace('.', '/', $file)).$this->getSuffix();
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
	 * @param string $file
	 * @return string
	 */
	public function getAsset($file = null) {
		return StringExpand::getFile($this->assetDir, $file);
	}
	
	/******************** ↓ 自定义视图函数 ↓ *******************************/

	/**
	 * 在视图中包含其他视图的方法
	 * @param string|array $names 视图文件名
	 * @param string|array $param 传给视图的内容
	 * @param bool|string $replace 是否替换
	 */
	public function extend($names, $param = null, $replace = TRUE) {
		if (!$replace) {
			$param = array_merge((array)$this->get('_extra'), (array)$param);
		}
		$this->set('_extra', $param);
		foreach (ArrayExpand::toFile((array)$names, '.') as $value) {
			$file = $this->getView($value);
			if (file_exists($file)) {
				include($file);
			} else {
				Error::out($file.' is not exist', __FILE__, __LINE__);
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
	 * 添加脚本
	 */
	public function script() {
		$args = func_get_args();
		if (empty($args)) {
			return;
		}
		if (count($args) == 1 && is_array($args[0])) {
			$args = $args[0];
		}
		$this->set('_extra', array_merge((array)$this->get('_extra'), $args));
	}

	/**
	 * 输出资源url
	 * @param string $file
	 */
	public function asset($file) {
		echo Url::toAsset($this->getAsset($file));
	}

	/**
	 * 输出完整的网址
	 * @param string $url
	 * @param array|string $extra
	 */
	public function url($url = null, $extra = null) {
		echo Url::to($url, $extra);
	}

	/**
	 * 判断是否包含url片段
	 * @param string $search
	 * @return bool
	 */
	public function hasUrl($search = null) {
		return Url::hasUri($search);
	}

	/**
	 * 输出执行方法后的数据
	 * @param string $func
	 */
    public function call($func) {
       $args = array();
       if (func_get_args() > 1) {
           $args = array_slice(func_get_args(), 1);
       }
       echo call_user_func_array($func, $args);
    }

	/**
	 * 输出格式化后的时间
	 * @param integer|string $time
	 */
	public function time($time = null) {
		if (is_null($time)) {
			$time = time();
		}
		echo TimeExpand::format($time);
	}

	/**
	 * 输出是多久以前
	 * @param int $time
	 */
	public function ago($time) {
		if (empty($time)) {
			return;
		}
		echo TimeExpand::isTimeAgo($time);
	}
	
	/**
	 * 直接输出
	 * @param string $key
	 * @param string $default
	 */
	public function ech($key, $default = null) {
		echo ArrayExpand::tostring($this->get($key, $default));
	}

	/*********************** ↑ 自定义视图函数 ↑ ***************************************/

	/**
	 * 加载视图
	 *
	 * @param string|array $name 视图的文件名 如果是array|null 将使用 $method引导视图 
	 * @param array|null $data 要传的数据 如果$name 为array 则$data = $name
	 */
	public function show($name = null, $data = null) {
		$content = $this->getContent($name, $data);
		if (!empty($this->layout)) {
			ob_start();
			include $this->getView($this->layout);
			$content = ob_get_contents();
			ob_end_clean();
		}
		EventManger::getInstance()->run('showView', $content);
		ResponseResult::make($content, $this->type, 200);
	}

	/**
	 * @param string|array $name
	 * @param array $data
	 * @return string
	 */
	protected function getContent($name = null, $data = null) {
		if (is_null($name)) {
			return $this->getContentByRoute();
		}
		if (is_array($name)) {
			$this->set($name);
			return $this->getContentByRoute();
		}
		if ($name instanceof \Closure) {
			return $this->getContentByObject($name);
		}
		if (!is_null($data)) {
			$this->set($data);
		}
		if (substr($name, 0, 1) === '@') {
			return substr($name, 1);
		}
		return $this->getContentByFile($name);
	}

	/**
	 * 如果传的是匿名函数  参数问题未解决
	 * @param \Closure $func
	 * @return string
	 */
	protected function getContentByObject($func) {
		ob_start();
		$data = $func();
		$content = ob_get_contents();
		ob_end_clean();
		if (empty($data)) {
			return $content;
		}
		return $data;
	}
	
	/**
	 * 根据路由判断
	 */
	protected function getContentByRoute() {
		list($class, $action) = Router::getClassAndAction();
		$name = str_replace('\\', '.', $class).'.'.$action;
		return $this->getContentByFile($name);
	}

	/**
	 * 
	 * @param $file
	 * @return string
	 */
	protected function getContentByFile($file) {
		ob_start();
		if (Config::getValue('view.mode') == 'common') {
			extract($this->get());
			include $this->getView($file);
		} else {
			include $this->getView($file);
		}
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	/**
	 * 根据路径判断
	 * @param string $file 路径
	 * @param integer $status 状态码
	 */
	public function showWithFile($file, $status = 200) {
		ResponseResult::make($this->getContentByFile($file), $this->type, $status);
	}
}