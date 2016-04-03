<?php 
namespace Zodream\Infrastructure;
/**
* 读写配置
* 
* @author Jason
*/
use Zodream\Infrastructure\Traits\SingletonPattern;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;


class Config extends MagicObject {
	
	use SingletonPattern;
	
	/**
	 * 判断配置文件是否存在
	 */
	public static function exist() {
		return file_exists(APP_DIR.'/Service/config/'.APP_MODULE.'.php');
	}
	
	private function __construct() {
		$this->reset();
	}
	
	/**
	 * 重新加载配置
	 */
	public function reset() {
		$configs = $this->_getConfig(dirname(dirname(__FILE__)). '/Service/config.php');
		$common  = $this->_getConfig(APP_DIR.'/Service/config/config.php');
		$personal = $this->_getConfig(APP_DIR.'/Service/config/'.APP_MODULE.'.php');
		$this->set(ArrayExpand::merge2D((array)$configs, (array)$common, (array)$personal));
	}

	/**
	 * 获取配置文件
	 * @param string $file
	 * @return array
     */
	private function _getConfig($file) {
		if (file_exists($file)) {
			$config = include($file);
			if (is_string($config)) {
				return $this->_getConfig($config);
			}
			return $config;
		}
		return array();
	}

	/**
	 * 根据方法换取多维中的一个值
	 * @param string $method
	 * @param array $value
	 * @return array|null|string
	 */
	public function getMultidimensional($method, array $value) {
		$length = count($value);
		if ($length < 1) {
			return $this->get($method);
		}
		if ($length > 1) {
			return $this->get($method . implode('.', $value));
		}
		if (!$this->has($method) || !isset($this->_data[$method][$value[0]])) {
			return null;
		}
		return $this->_data[$method][$value[0]];
	}
	
	public function __call($method, $value) {
		$this->getMultidimensional($method, $value);
	}

	/**
	 * 静态方法获取
	 * @param null $key
	 * @param null $default
	 * @return array|string
	 */
	public static function getValue($key = null, $default = null) {
		return static::getInstance()->get($key, $default);
	}

	/**
	 *
	 * @param string $method
	 * @param array $value
	 * @return mixed
	 */
	public static function __callStatic($method, $value) {
		return static::getInstance()->getMultidimensional($method, $value);
	}
}