<?php 
namespace Zodream\Infrastructure\Response;
/**
* 响应
* 
* @author Jason
* @time 2015-12-19
*/
use Zodream\Infrastructure\Traits\SingletonPattern;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
use Zodream\Infrastructure\FileSystem;
use Zodream\Domain\Html\Script;
use Zodream\Domain\Routing\UrlGenerator;
use Zodream\Domain\Routing\Router;

defined('VIEW_DIR') or define('VIEW_DIR', '/');

class View extends Obj {
	use SingletonPattern;
	
	/**
	 * 在视图中包含其他视图的方法
	 * @param string|array $names 视图文件名
	 * @param string|array $param 传给视图的内容
	 * @param string $replace 是否替换
	 */
	public function extend($names, $param = null, $replace = TRUE) {
		if (!$replace) {
			$param = array_merge((array)$this->getExtra(), (array)$param);
		}
		$this->set('_extra', $param);
		foreach (ArrayExpand::toFile($names, '.') as $value) {
			$file = FileSystem::view($value);
			if (file_exists($file)) {
				include($file);
			} else {
				throw new Error('NOT FIND FILE:'.$file);
			}
		}
	}
	
	/**
	 * 输出脚本
	 */
	public function jcs() {
		$args   = func_get_args();
		$args[] = $this->get('_extra', array());
		Script::make(ArrayExpand::sort($args));
	}
	
	/**
	 * 输出资源url
	 * @param unknown $file
	 * @param string $isView
	 */
	public function asset($file, $isView = TRUE) {
		if ($isView) {
			$file = strtolower(APP_MODULE).'/'.VIEW_DIR.ltrim($file, '/');
		} else {
			$file = 'assets/'.ltrim($file, '/');
		}
		echo UrlGenerator::to($file);
	}
	
	public function url($url) {
		echo UrlGenerator::to($url);
	}
	
	/**
	 * 直接输出
	 * @param unknown $key
	 */
	public function ech($key) {
		echo ArrayExpand::tostring($this->get($key));
	}
	
	/**
	 * 加载视图
	 *
	 * @param string|array $name 视图的文件名 如果是array|null 将使用 $method引导视图 
	 * @param array|null $data 要传的数据 如果$name 为array 则$data = $name
	 * @param system $method 获取方法
	 */
	public function show($name = null, $data = null) {
		if (is_array($name)) {
			$data = $name;
			$name = null;
		}
		if (!empty($data)) {
			$this->set($data);
		}
		if (empty($name)) {
			$name = str_replace(array('\\', '::', APP_MODULE.'.Head.', APP_CONTROLLER, APP_ACTION), array('.', '.'), Router::$method);
		}
		if (APP_API) {
			$this->ajaxJson($this->get());
		} else {
			ob_start();
			include(FileSystem::view($name));
			$content = ob_get_contents();
			ob_end_clean();
			$this->showGzip($content);
		}
	}
	
	public function showGzip($content) {
		if (extension_loaded('zlib')) {
			if (!headers_sent() && isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
					strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) {
						ob_start('ob_gzhandler');
					} else {
						ob_start();
					}
		} else {
			ob_start();
		}
		header( 'Content-Type:text/html;charset=utf-8' );
		ob_implicit_flush(FALSE);
		echo $content;
		ob_end_flush();
		exit;
	}
}