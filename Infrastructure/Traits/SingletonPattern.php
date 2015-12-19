<?php
namespace Zodream\Infrastructure\Traits;
/**
 * 单例模式
 * @author Jason
 *
 */

trait SingletonPattern {
	protected static $instance;
	/**
	 * 单例
	 */
	public static function getInstance() {
		if (is_null(static::$instance)) {
			static::$instance = new static;
		}
		return static::$instance;
	}
}