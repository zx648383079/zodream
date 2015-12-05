<?php 
namespace Zodream\Body;
/*
* 读写配置
* 
* @author Jason
* @time 2015-11.29
*/
use Zodream\Body\Object\Obj;
use Zodream\Body\Object\Arr;

class Config extends Obj {
	protected static $instance = null;
	/**
	 * 
	 */
	public static function getInstance() {
		if (is_null(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	
	/**
	 * 判断配置文件是否存在
	 */
	public static function exist() {
		return file_exists(APP_DIR.'Foot/config.php');
	}
	
	private function __construct() {
		$this->reset();
	}
	
	/**
	 * 重新加载配置
	 */
	public function reset() {
		$configs = $this->_getConfig(dirname(dirname(__FILE__)). '/Foot/config.php');
		$tem = $this->_getConfig(APP_DIR.'Foot/config.php');
		$this->set(Arr::merge((array)$configs, (array)$tem));
	}
	
	private function _getConfig($file) {
		if (file_exists($file)) {
			$tem = include($file);
			if (is_string($tem)) {
				return $this->_getConfig($tem);
			}
			return $tem;
		}
		return array();
	}
	
	/**
	 * 根据方法换取多维中的一个值
	 * @param unknown $method
	 * @param unknown $value
	 */
	public function getMultidimensional($method, $value) {
		$length = count($value);
		if ($length < 1) {
			return $this->get($method);
		}
		if ($length > 1) {
			return $this->get($method. implode('.', $value));
		}
		
		if (!$this->has($method) || !isset($this->_data[$method][$value[0]])) {
			return null;
		}
		return $this->_data[$method][$value[0]];
	}
	
	public function __call($method, $value) {
		$this->getMultidimensional($method, $value);
	}
	
	public static function __callstatic($method, $value) {
		return static::getInstance()->getMultidimensional($method, $value);
	}
}