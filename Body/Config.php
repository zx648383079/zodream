<?php 
namespace App\Body;
/*
* 读写配置
* 
* @author Jason
* @time 2015-11.29
*/
use App\Body\Object\Obj;

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
	
	private function __construct() {
		$file = dirname(dirname(__FILE__)). '/Foot/config.php';
		$configs = array();
		if (file_exists($file)) {
			$configs = include($file);
		}
		$file = APP_DIR.'Foot/config.php';
		if (file_exists($file)) {
			$tem = include($file);
			foreach ($tem as $key => $value) {
				if (!array_key_exists($key, $configs) || !is_array($value)) {
					$configs[$key] = $value;
					continue;
				}
				foreach ($value as $k => $val) {
					$configs[$key][$k] = $val;
				}
			}
		}
		$this->set($configs);
	}
}