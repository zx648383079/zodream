<?php 
namespace Zodream\Domain\View;
/**
* 响应
* 
* @author Jason
* @time 2015-12-19
*/
use Zodream\Domain\View\Engine\DreamEngine;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\DomainObject\EngineObject;
use Zodream\Infrastructure\Error\Error;
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\Request;
use Zodream\Domain\Routing\Router;
defined('APP_ROOT') or define('APP_ROOT', Request::server('DOCUMENT_ROOT'));

class View1 extends MagicObject {

	/**
	 * @var EngineObject
	 */
	protected $engine;
	
	protected $path;
	
	public function __construct() {
		$class = Config::getValue('view.driver', DreamEngine::class);
		if (!class_exists($class)) {
			Error::out('ENGINE NOT CLASS', __FILE__, __LINE__);
		}
		$this->engine = new $class;
	}
	
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}

	/**
	 * 如果传的是匿名函数  参数问题未解决
	 * @param \Closure $func
	 * @return string
	 */
	protected function getContentByObject($func) {
		ob_start();
		$data = call_user_func($func);
		$content = ob_get_contents();
		ob_end_clean();
		if (empty($data)) {
			return $content;
		}
		return $data;
	}

	public function render(callable $callback = null) {
		try {
			$contents = $this->renderContents();
			$response = isset($callback) ? call_user_func($callback, $this, $contents) : null;
			return ! is_null($response) ? $response : $contents;
		} catch (\Exception $e) {
			throw $e;
		}
	}

	protected function renderContents() {
		if (is_callable($this->path)) {
			return $this->getContentByObject($this->path);
		}
		if (substr($this->path, 0, 1) === '@') {
			return substr($this->path, 1);
		}
		return $this->engine->get($this->path, $this->get());
	}
}