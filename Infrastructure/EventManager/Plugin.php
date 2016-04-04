<?php
namespace Zodream\infrastructure\EventManager;
/**
 * 插件功能
 *
 * @author Jason
 */
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\Traits\SingletonPattern;

class Plugin extends MagicObject{

	use SingletonPattern;
	
	//私有克隆
	protected function __clone() {}
	
	private function __construct() {
	
	}
	
	public function find() {
		$pluginPath = APP_DIR.'/plugin/';
		if (!is_dir($pluginPath)) {
			return ;
		}
	}
	
	/**
	 * 执行插件
	 * @param string $key
	 * @param string $arg
	 */
	public function execute($key = null, $arg = null) {
		$plugins = $this->get($key);
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
			foreach ((array) current($plugins) as $plugin) {
				if (!is_null($plugin['function'])) {
					call_user_func_array($plugin['function'], array_slice($args, 0, (int) $plugin['accept']));
				}
			}
		} while ( next($plugins) !== false );
	}

	/**
	 * 添加插件
	 * @param string $key 关键字
	 * @param string|object $arg 方法
	 * @param boolean $before 是否放到最前 默认false
	 * @param integer $accept 接受的值
	 */
	public function add($key, $arg, $before = FALSE, $accept = 1) {
		$plugin = array(
				'function' => $arg,
				'accept'   => $accept
		);
		$plugins = $this->get($key, array());
		if ($before) {
			array_unshift($plugins, $plugin);
		} else {
			$plugins[] = $plugin;
		}
		$this->set($key, $plugins);
	}
}