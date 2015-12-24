<?php
namespace Zodream\Domain;
/**
 * 自动加载功能
 *
 * @author Jason
 */
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Log;
use Zodream\Infrastructure\Traits\SingletonPattern;
use Zodream\Infrastructure\MagicObject;

class Autoload extends MagicObject {
	
	use SingletonPattern;
	
	protected $_registerAlias = false;
	
	private function __construct() {
		$this->set(Config::getInstance()->get('alias', array()));
	}
	
	/**
	 * 注册别名
	 */
	public function registerAlias() {
		if (!$this->_registerAlias) {
			spl_autoload_register(array($this, '_load'), true, true);
			$this->_registerAlias = TRUE;
		}
	}
	
	/**
	 * 设置别名
	 * @param string $alias
	 */
	protected function _load($alias) {
		if ($this->has($alias)) {
			return class_alias($this->get($alias), $alias);
		}
	}
	
	/**
	 * 自定义错误输出
	 */
	public function setError() {
		set_error_handler(array($this, '_error'));          //自定义错误输出
	}
	
	protected function _error($errno, $errstr, $errfile, $errline) {
		$str = '错误级别：'.$errno.'错误的信息：'.$errstr.'<br>发生在 '.$errfile.' 第 '.$errline.' 行！当前网址：'.Url::get();
		Log::out('txt', $str);
		if (!defined('DEBUG') || !DEBUG) {
			$str = '出错了！';
		}
		/*::getInstance()->show('404', array(
				'error' => $str
		));*/
	}
	
	/**
	 * 自定义程序结束时输出
	 */
	public function shutDown() {
		register_shutdown_function(array($this, '_shutDown'));   //程序结束时输出
	}
	
	protected function _shutDown() {
		$error = error_get_last();
		if (empty($error)) {
			return;
		}
		$str = '错误类型：'.$error['type'].'错误的信息：'.$error['message'].'<br>发生在 '.$error['file'].' 第 '.$error['line'].' 行！当前网址：'.Url::get();
		Log::out('txt', $str);
		if (!defined('DEBUG') || !DEBUG) {
			$str = '出错了！';
		}
		/*Response::getInstance()->show('404', array(
				'error' => $str
		));*/
	}
	
	private function __clone() {
		
	}
}