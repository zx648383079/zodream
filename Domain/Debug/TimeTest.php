<?php
namespace Zodream\Domain\Debug;

class TimeTest {
	protected static $_time;
	
	public static function begin() {
		$mtime = explode(" ", microtime());
		return self::$_time = $mtime[1] + $mtime[0];
	}
	
	public static function end() {
		$mtime = explode(" ", microtime());
		$endtime = $mtime[1] + $mtime[0];
		$totaltime = ($endtime - self::$_time);
		$totaltime = number_format($totaltime, 7);
		return $totaltime;
	}
}