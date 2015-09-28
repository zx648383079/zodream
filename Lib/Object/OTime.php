<?php
namespace App\Lib\Object;
/******
时间处理类
*******/

class OTime implements IBase
{
	/***
	返回当前时间
	***/
	public static function Now($format = null)
	{
		date_default_timezone_set('Etc/GMT-8');     //这里设置了时区
		
		$time = time();
		
		if(!empty($format))
		{
			$time = date($format , $time);
		}
		return $time;
	}
	
	public static function to($arg)
	{
		return date( 'Y-m-d H:i:s' , $arg);
	}
	
	public function time()
	{
		//php获取今日开始时间戳和结束时间戳
		$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
		$endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
		
		//php获取昨日起始时间戳和结束时间戳
		
		$beginYesterday=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
		$endYesterday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
		
		//php获取上周起始时间戳和结束时间戳
		
		$beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
		$endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
		
		//php获取本月起始时间戳和结束时间戳
		
		$beginThismonth=mktime(0,0,0,date('m'),1,date('Y'));
		$endThismonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
	}
}