<?php 
namespace App\Body;
/*
* 日志类
* 
* @author Jason
* @time 2015-11.29
*/

class Log {
	public static function write($logs) {
		$log = '';
		if (is_array($logs)) {
			foreach ($logs as $k => $r) {
				$log .= "{$k}='{$r}',";
			}
		} else {
			$log = $logs;
		}
		$logFile = dirname(APP_DIR).'/log/'.date('Y-m-d').'.txt';
		$log     = date('Y-m-d H:i:s').' >>> '.$log."\r\n";
		file_put_contents($logFile, $log, FILE_APPEND );
	}
}