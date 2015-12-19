<?php
namespace Zodream\Domain\Tesst;

class TimeTest {
	protected static $_time;
	
	public static function begin() {
		self::$_time = microtime(true);
	}
	
	public static function end() {
		return microtime(true) - self::$_time;
	}
}