<?php
namespace App\Head;
/*
 * 插件功能
 *
 * @author Jason
 * @time 2015-12-1
 */
use App\Body\Object\Obj;

class Plugin extends Obj {
	protected static $instance = null;
	
	/**
	 * 公共静态方法获取实例化的对象
	 */
	public static function getInstance() {
		if (!(self::$instance instanceof self)) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	//私有克隆
	protected function __clone() {}
	
	private function __construct() {
	
	}
	
	private function _findPlugin() {
		$pluginPath = APP_DIR.'/plugin/';
		if (!is_dir($pluginPath)) {
			return ;
		}
	
	}
	
	public function get($key = null, $arg = null) {
		$plugins = parent::get($key);
		if (!empty($key)) {
			$plugins = array($plugins);
		}
		if (empty($plugins)) {
			return;
		}
		$args = array();
		if ( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) ) {
			$args[] = & $arg[0];
		} else {
			$args[] = $arg;
		}
		for ($i = 2, $count = func_num_args(); $i < $count; $i++) {
			$args[] = func_get_arg($i);
		}
		do {
			foreach ((array) current($plugins) as $plugin)
				if (!is_null($plugin['function'])) {
					call_user_func_array($plugin['function'], array_slice($args, 0, (int) $plugin['accept']));
				}
		} while ( next($plugins) !== false );
	}
	
	public function set($key, $arg, $priority = 10, $accept = 1) {
		$plugin = array(
				'function' => $arg,
				'accept'   => $accept
		);
		if ($this->has($key)) {
			$this->_data[$key][] = $plugin;
		} else {
			parent::set($key, array(
					$plugin
			));
		}
	}
}