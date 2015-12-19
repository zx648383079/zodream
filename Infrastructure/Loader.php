<?php 
namespace Zodream\Infrastructure;
/**
* 反射调用类
* 
* @author Jason
*/

class Loader extends MagicObject {
	/**
	 * 添加数据类
	 * @param unknown $model
	 */
	public function model($models) {
		$this->_add($models, APP_MODULE.'\\Model\\', APP_MODEL);
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
		$this->_add($library, APP_MODULE.'\\Lib\\');
	}
	
	/**
	 * 添加控件
	 * @param string|array $names 名字
	 * @param string $pre 前缀
	 * @param string $after 后缀
	 * @param string $up 是否大写首字母 默认 true
	 */
	private function _add($names, $pre = '', $after = '', $up = true) {
		if (is_string($names)) {
			$names = explode(',', $names);
		}
		foreach ($names as $key => $value) {
			$class = $pre. ($up ? ucfirst($value) : $value). $after;
			if (class_exists($class)) {
				$this->set(is_numeric($key) ? (str_replace('\\', '_', $value).$after) : $key, new $class);
			} else {
				exit('Error: Could not load ' . $class . '!');
			}
		}
	}
}