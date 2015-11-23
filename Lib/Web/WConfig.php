<?php
namespace App\Lib\Web;

use App\Lib\Object\OBase;

class WConfig extends OBase {
	
	private static $_instance;
	/**
	 * 单例模式
	 * @return \App\Lib\Web\WResponse
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function __construct() {
		$file = dirname(dirname(dirname(__FILE__))). '/config/config.php';
		$configs = array();
		if (file_exists($file)) {
			$configs = include($file);
		}
		$file = APP_DIR.'/config/config.php';
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
		$this->_data = $configs;
	}
}