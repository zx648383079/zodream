<?php 
namespace Zodream\Infrastructure\ObjectExpand;
/**
* time 的扩展
* 
* @author Jason
*/
class TimeExpand {

	/**
	 * 将时间转换成字符串格式
	 * @param null $time
	 * @param string $format
	 * @return string
	 */
	public static function format($time = null, $format = 'Y-m-d H:i:s') {
		date_default_timezone_set('Etc/GMT-8');     //这里设置了时区
		if (!is_numeric($time)) {
			$format = $time;
			$time = time();
		}
		if ($time == null) {
			$time = time();
		}
		return date($format, $time);
	}

	const TODAY = 0;
	const YESTERDAY = 1;
	const WEEK = 2;
	const LASTWEEK = 3;
	const MONTH = 4;
	const LASTMONTH = 5;
	const YEAR = 6;
	const LASTYEAR = 7;

	/**
	 * 获取开始时间和结束时间
	 * @param int $kind 类型
	 * @return array 包含开始时间可结束时间
	 */
	public static function getBeginAndEndTime($kind = 0) {
		switch ($kind) {
			case self::TODAY:
				return array(
					mktime(0, 0, 0, date('m'), date('d'), date('Y')),
					mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1
				);
			case self::YESTERDAY:
				return array(
					mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')),
					mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1
				);
			case self::WEEK:
				// 周一作为第一天
				$week = date('w');
				if ($week == 0) {
					$week = 7;
				}
				return [
					mktime(0, 0, 0, date('m'), date('d') - $week + 1, date('Y')),
					mktime(0, 0, 0, date('m'), date('d') + 7 - $week, date('Y')),
				];
			case self::LASTWEEK:
				$week = date('w');
				if ($week == 0) {
					$week = 7;
				}
				return array(
					mktime(0, 0, 0, date('m'), date('d') - $week - 6, date('Y')),
					mktime(23, 59, 59, date('m'), date('d') - $week, date('Y'))
				);
			case self::MONTH:
				return array(
					mktime(0, 0, 0, date('m'), 1, date('Y')),
					mktime(23, 59, 59, date('m'), date('t'), date('Y'))
				);
			case self::LASTMONTH:
				return array(
					mktime(0, 0, 0, date('m') - 1, 1, date('Y')),
					mktime(23, 59, 59, date('m'), 0, date('Y'))
				);
			case self::YEAR:
				return array(
					mktime(0, 0, 0, 1, 1, date('Y')),
					mktime(23, 59, 59, 12, 31, date('Y'))
				);
			case self::LASTYEAR:
				return array(
					mktime(0, 0, 0, 1, 1, date('Y') - 1),
					mktime(23, 59, 59, 12, 31, date('Y') - 1)
				);
		}
		return array(
			0,
			time()
		);
	}

	/**
	 * 获取时间是多久以前
	 * @param $time
	 * @return int|string
	 */
	public static function isTimeAgo($time){
		$differ = time() - $time;
		if ($differ < 1) {
			$differ = 1;
		}
		$tokens = array (
				31536000 => '年',
				2592000  => '月',
				604800   => '周',
				86400    => '天',
				3600     => '小时',
				60       => '分钟',
				1        => '秒钟'
		);
	
		foreach ($tokens as $unit => $text) {
			if ($differ < $unit) continue;
			$numberOfUnits = floor($differ / $unit);
			return $numberOfUnits.' '.$text.'前';
		}
		return self::format($time);
	}
	
	/**
	 * 本周一
	 * @param int $timestamp 某个月的某一个时间戳，默认为当前时间
	 * @param string $date 日期格式
	 * @param bool|true $zero 时间是否归零到凌晨零点
	 * @return bool|int|string
	 */
	static public function thisMonday($timestamp=0,$date='',$zero=true){
		//如果未设置时间，则使用当前时间
		$timestamp || $timestamp = time();
		//周一的时间
		$time = $timestamp-86400*(date('N',$timestamp)-1);
		//归零处理
		$zero && $time = self::_zero($time);
		//如果设置了日期格式，按格式返回
		return $date ? date($date,$time) : $time;
	
	}
	
	/**
	 * 本周日
	 * @param int $timestamp 某个月的某一个时间戳，默认为当前时间
	 * @param string $date 日期格式
	 * @param bool|true $zero 时间是否归零到凌晨零点
	 * @return bool|int|string
	 */
	static public function thisSunday($timestamp=0,$date='',$zero=true){
		//如果未设置时间，则使用当前时间
		$timestamp || $timestamp = time();
		//周日
		$time = $timestamp+86400*(7-date('N',$timestamp));
		//归零处理
		$zero && $time = self::_zero($time);
		//如果设置了日期格式，按格式返回
		return $date ? date($date,$time) : $time;
	}
	
	/**
	 * 上周一
	 * @param int $timestamp 某个月的某一个时间戳，默认为当前时间
	 * @param string $date 日期格式
	 * @param bool|true $zero 时间是否归零到凌晨零点
	 * @return bool|int|string
	 */
	static public function lastMonday($timestamp=0,$date='',$zero=true){
		//如果未设置时间，则使用当前时间
		$timestamp || $timestamp = time();
		//上周同一时间
		$timestamp = $timestamp-86400*7;
		//周一的时间
		$time = $timestamp-86400*(date('N',$timestamp)-1);
		//归零处理
		$zero && $time = self::_zero($time);
		//如果设置了日期格式，按格式返回
		return $date ? date($date,$time) : $time;
	}
	
	/**
	 * 上个星期天
	 * @param int $timestamp 某个月的某一个时间戳，默认为当前时间
	 * @param string $date 日期格式
	 * @param bool|true $zero 时间是否归零到凌晨零点
	 * @return bool|int|string
	 */
	static public function lastSunday($timestamp=0,$date='',$zero=true){
		//如果未设置时间，则使用当前时间
		$timestamp || $timestamp = time();
		//上周同一时间
		$timestamp = $timestamp-86400*7;
		//周日
		$time = $timestamp+86400*(7-date('N',$timestamp));
		//归零处理
		$zero && $time = self::_zero($time);
		//如果设置了日期格式，按格式返回
		return $date ? date($date,$time) : $time;
	
	}
	
	/**
	 * 这个月的第一天
	 * @param int $timestamp 某个月的某一个时间戳，默认为当前时间
	 * @param string $date 日期格式
	 * @param bool|true $zero 时间是否归零到凌晨零点
	 * @return bool|int|string
	 */
	static public function monthFirstDay($timestamp=0,$date='',$zero=true){
		//如果未设置时间，则使用当前时间
		$timestamp || $timestamp = time();
		//周一的时间
		$time = mktime(date('H',$timestamp),date('i',$timestamp),date('s',$timestamp),date('m',$timestamp),1,date('Y',$timestamp));
		//归零处理
		$zero && $time = self::_zero($time);
		//如果设置了日期格式，按格式返回
		return $date ? date($date,$time) : $time;
	}
	
	
	/**
	 * 这个月的最后一天
	 * @param int $timestamp 某个月的某一个时间戳，默认为当前时间
	 * @param string $date 日期格式
	 * @param bool|true $zero 时间是否归零到凌晨零点
	 * @return bool|int|string
	 */
	static public function monthLastDay($timestamp=0,$date='',$zero=true){
		//如果未设置时间，则使用当前时间
		$timestamp || $timestamp = time();
		//周一的时间
		$time = mktime(date('H',$timestamp),date('i',$timestamp),date('s',$timestamp),date('m',$timestamp),date('t',$timestamp),date('Y',$timestamp));
		//归零处理
		$zero && $time = self::_zero($time);
		//如果设置了日期格式，按格式返回
		return $date ? date($date,$time) : $time;
	}
	
	/**
	 * 上个月的第一天
	 * @param int $timestamp 某个月的某一个时间戳，默认为当前时间
	 * @param string $date 日期格式
	 * @param bool|true $zero 时间是否归零到凌晨零点
	 * @return bool|int|string
	 */
	static public function lastMonthFirstDay($timestamp=0,$date='',$zero=true){
		//如果未设置时间，则使用当前时间
		$timestamp || $timestamp = time();
		//周一的时间
		$time = mktime(date('H',$timestamp),date('i',$timestamp),date('s',$timestamp),date('m',$timestamp)-1,1,date('Y',$timestamp));
		//归零处理
		$zero && $time = self::_zero($time);
		//如果设置了日期格式，按格式返回
		return $date ? date($date,$time) : $time;
	
	}
	
	/**
	 * 上个月的最后一天
	 * @param int $timestamp 某个月的某一个时间戳，默认为当前时间
	 * @param string $date 日期格式
	 * @param bool|true $zero 时间是否归零到凌晨零点
	 * @return bool|int|string
	 */
	static public function lastMonthLastDay($timestamp=0,$date='',$zero=true){
	
		//如果未设置时间，则使用当前时间
		!$timestamp || $timestamp = time();
		//上月第一天的当前时间
		$timestamp = mktime(date('H',$timestamp),date('i',$timestamp),date('s',$timestamp),date('m',$timestamp)-1,1,date('Y',$timestamp));
		//取得最后一天时间
		$time = mktime(date('H',$timestamp),date('i',$timestamp),date('s',$timestamp),date('m',$timestamp),date('t',$timestamp),date('Y',$timestamp));
		//归零处理
		$zero && $time = self::_zero($time);
		//如果设置了日期格式，按格式返回
		return $date ? date($date,$time) : $time;
	
	}
	
	/**
	 * 时间归零，回到当天的00:00:00
	 * @param $timestamp
	 * @return int
	 */
	static private function _zero($timestamp)
	{
		return strtotime(date('Y-m-d',$timestamp));
	}
}