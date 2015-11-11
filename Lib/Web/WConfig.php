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
		$this->_data = include(APP_DIR.'/config/config.php');
	}
}