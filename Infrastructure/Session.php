<?php 
namespace Zodream\Infrastructure;
/**
* session 读写类
* 
* @author Jason
* @time 2015-12-3
*/

class Session {
	/**
	 * @var \Zodream\Infrastructure\SessionExpand\Session
	 */
	protected static $instance;
	public static function getInstance() {
		if (empty(static::$instance)) {
			$class = Config::getValue('session.driver');
			if (empty($class) || !class_exists($class)) {
				$class = \Zodream\Infrastructure\SessionExpand\Session::class;
			}
			static::$instance = new $class;
		}
		return static::$instance;
	}

	public static function getValue($name, $default = null) {
		return static::getInstance()->get($name, $default);
	}

	public static function setValue($name, $value = null) {
		static::getInstance()->set($name, $value);
	}
}