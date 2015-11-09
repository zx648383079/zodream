<?php
namespace App\Lib;
/**
 * 加载器
 * @author zodream
 *
 */

final class Loader {
	private $data = array();
	
	/**
	 * 根据key获取类
	 * @param unknown $key
	 */
	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : NULL);
	}
	
	/**
	 * 添加类
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function set($key, $value) {
		if (is_string($value)) {
			$this->data[$key] = new $value;
		} else {
			$this->data[$key] = $value;
		}
	}
	
	public function __get($key) {
		return $this->get($key);
	}
	
	public function __set($key, $value) {
		$this->set($key, $value);
	}
	
	/**
	 * 判断是否有
	 * @param unknown $key
	 */
	public function has($key) {
		return isset($this->data[$key]);
	}
	
	/**
	 * 添加数据类
	 * @param unknown $model
	 */
	public function model($model) {
		$class = APP_MODULE.'\\Model\\'.ucfirst($model). APP_MODEL;
		if (class_exists($class)) {
			$this->set($model.'Model', new $class);
		} else {
			exit('Error: Could not load model ' . $model . '!');
		}
	}
	
	/**
	 * 添加插件 未实例化
	 * @param unknown $plugin
	 */
	public function plugin($plugin) {
		$file = APP_DIR. '/Lib/Plugin/'. $plugin. '.php';
		if (file_exists($file)) {
			include_once($file);
		} else {
			exit('Error: Could not load plugin ' . $library . '!');
		}
	}
	
	/**
	 * 添加类
	 * @param unknown $library
	 */
	public function library($library) {
		$class = APP_MODULE.'\\Lib\\'.ucfirst($library);
		if (class_exists($class)) {
			$this->set(str_replace('\\', '_', $library), new $class);
		} else {
			exit('Error: Could not load library ' . $library . '!');
		}
	}
}