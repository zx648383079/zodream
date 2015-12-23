<?php
namespace Zodream\Domain\Debug;

class Memory {
	public static function get() {
		memory_get_usage();      //内存使用量
		memory_get_peak_usage(); //内存使用峰值
		getrusage();             //CUP使用情况
	}
}