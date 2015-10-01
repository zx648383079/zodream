<?php
namespace App\Lib\Object;

/**
* 字符串的操作
*/

class OString implements IBase
{
	public static function toArray($str, $link = ' ', $num = 1 ,$default = null)
	{
		$arr = explode($link, $str, $num);
		for ($i = 0 ; $i < $num ; $i++) 
		{
			if(!isset($arr[i]) || $arr[i] === '')
			{
				if(is_array($default))
				{
					$arr[i] = $default[i];
				}else {
					$arr[i] = $default;
				}
			} 
		}
		
		return $arr;
	}
	
	public static function abslength($str)
	{
		if(empty($str)){
			return 0;
		}
		if(function_exists('mb_strlen')){
			return mb_strlen($str,'utf-8');
		}
		else {
			preg_match_all("/./u", $str, $ar);
			return count($ar[0]);
		}
	}
	
	public static function utf8_substr($str , $start = 0, $length = 0)
	{
		if(empty($str))
		{
			return false;
		}
		if (function_exists('mb_substr'))
		{
			if(func_num_args() >= 3) {
				$end = func_get_arg(2);
				return mb_substr($str,$start,$end,'utf-8');
			}
			else {
				mb_internal_encoding("UTF-8");
				return mb_substr($str,$start);
			}      
	
		}
		else {
			$null = "";
			preg_match_all("/./u", $str, $ar);
			if(func_num_args() >= 3) 
			{
				$end = func_get_arg(2);
				return join($null, array_slice($ar[0],$start,$end));
			}
			else {
				return join($null, array_slice($ar[0],$start));
			}
		}
	}
	
	/* 
	* 中文截取，支持gb2312,gbk,utf-8,big5 
	* 
	* @param string $str 要截取的字串 
	* @param int $start 截取起始位置 
	* @param int $length 截取长度 
	* @param string $charset utf-8|gb2312|gbk|big5 编码 
	* @param $suffix 是否加尾缀 
	*/
	
	public function csubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) 
	{ 
		if(function_exists("mb_substr")) 
		{ 
			if(mb_strlen($str, $charset) <= $length) return $str; 
			$slice = mb_substr($str, $start, $length, $charset); 
		} 
		else
		{ 
			$re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/"; 
			$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/"; 
			$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/"; 
			$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/"; 
			preg_match_all($re[$charset], $str, $match); 
			if(count($match[0]) <= $length) return $str; 
			$slice = join("",array_slice($match[0], $start, $length)); 
		} 
		if($suffix) return $slice."…"; 
		return $slice; 
	} 
}