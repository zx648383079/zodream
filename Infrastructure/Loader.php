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
	 * @param string $model
	 */
	public function model($models, $action = null) {
		$this->_add($models, $action, 'Domain\\Model\\', APP_MODEL);
	}
	
	public function form($forms, $action = null) {
		$this->_data($forms, $action, 'Domain\\Form\\', APP_FORM);
	}
	
	/**
	 * 添加插件 未实例化
	 * @param string $plugin
	 */
	public function plugin($plugin) {
		$file = APP_DIR. '/Domain/Plugin/'. $plugin. '.php';
		if (file_exists($file)) {
			include_once($file);
		} else {
			exit('Error: Could not load plugin ' . $plugin . '!');
		}
	}
	
	/**
	 * 添加类
	 * @param string $library
	 */
	public function library($library, $action = null) {
		$this->_add($library, $action, 'Domain\\');
	}
	
	/**
	 * 添加控件
	 * @param string|array $names 名字
	 * @param string $pre 前缀
	 * @param string $after 后缀
	 * @param string $up 是否大写首字母 默认 true
	 */
	private function _add($names, $action = null, $pre = '', $after = '', $up = true) {
		if (is_string($names)) {
			$names = explode(',', $names);
		}
		foreach ($names as $key => $value) {
			$class = $pre. ($up ? ucfirst($value) : $value). $after;
			if (class_exists($class)) {
				$instance = new $class;
				if(empty($action)) {
					call_user_func(array($instance, $action));
				}
				$this->set(is_numeric($key) ? (str_replace('\\', '_', $value).$after) : $key, $instance);
			} else {
				exit('Error: Could not load ' . $class . '!');
			}
		}
	}
}