<?php 
namespace App\Body\Object;
/*
* time 的扩展
* 
* @author Jason
* @time 2015-11.29
*/
class Tim {
	/***
	 返回当前时间
	 ***/
	public static function Now($format = null, $time = null) {
		date_default_timezone_set('Etc/GMT-8');     //这里设置了时区
		if (empty($time)) {
			$time = time();
		}
		if (!empty($format)) {
			$time = date($format, $time);
		}
		return $time;
	}
	
	public static function getBeginAndEndTime() {
		//php获取今日开始时间戳和结束时间戳
		$beginToday     = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$endToday       = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
	
		//php获取昨日起始时间戳和结束时间戳
	
		$beginYesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
		$endYesterday   = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
	
		//php获取上周起始时间戳和结束时间戳
	
		$beginLastweek  = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 7, date('Y'));
		$endLastweek    = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 7, date('Y'));
	
		//php获取本月起始时间戳和结束时间戳
	
		$beginThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
		$endThismonth   = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
	}
	
	public static function isTimeAgo($time){
		$time   = time() - $time; // to get the time since that moment
		$time   = ($time<1)? 1 : $time;
		$tokens = array (
				31536000 => 'year',
				2592000  => 'month',
				604800   => 'week',
				86400    => 'day',
				3600     => 'hour',
				60       => 'minute',
				1        => 'second'
		);
	
		foreach ($tokens as $unit => $text) {
			if ($time < $unit) continue;
			$numberOfUnits = floor($time / $unit);
			return $numberOfUnits.' '.$text.(($numberOfUnits>1) ? 's' : '');
		}
	}
}