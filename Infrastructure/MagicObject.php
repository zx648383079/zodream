<?php 
namespace Zodream\Infrastructure;
/**
* object 的扩展
* 主要增加get、set、has 方法，及使用魔术变量
* 
* @author Jason
*/
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;

class MagicObject {
	
	protected $_data = array();
	
	/**
	 * 获取值
	 * @param string $key 关键字
	 * @param string $default 默认返回值
	 */
	public function get($key = null, $default = null) {
		if (empty($key)) {
			return $this->_data;
		}
		if (array_key_exists($key, $this->_data)) {
			return $this->_data[$key];
		}
		$result = ArrayExpand::getChild($key, $this->_data, is_object($default) ? null : $default);
		if (is_object($default)) {
			return $default($result);
		} else {
			return $result;
		}
	}
	
	/**
	 * 设置值
	 * @param string|array $key
	 * @param string $value
	 */
	public function set($key, $value = null) {
		if (is_object($key)) {
			$key = (array)$key;
		}
		if (is_array($key)) {
			$this->_data = array_merge($this->_data, $key);
		} else {
			$this->_data[$key] = $value;
		}
	}
	
	/**
	 * 删除键 目前只支持一维
	 * @param unknown $tag
	 */
	public function delete($tag) {
		foreach (func_get_args() as $value) {
			unset($this->_data[$value]);
		}
	}
	
	/**
	 * 判断是否有
	 * @param string $key
	 */
	public function has($key) {
		return isset($this->_data[$key]);
	}
	
	public function __get($key) {
		return $this->get($key);
	}
	
	public function __set($key, $value) {
		$this->set($key, $value);
	}
}