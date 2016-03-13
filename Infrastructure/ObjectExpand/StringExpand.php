<?php 
namespace Zodream\Infrastructure\ObjectExpand;
use Zodream\Infrastructure\Error;

/**
* string 的扩展
* 
* @author Jason
*/
class StringExpand {

	/**
	 * 生成更加真实的随机字符串
	 *
	 * @param  int  $length
	 * @return string
	 */
	public static function random($length = 16) {
		if (function_exists('str_random')) {
			return str_random($length);
		}
		$string = '';
		while (($len = strlen($string)) < $length) {
			$size = $length - $len;
			$bytes = static::randomBytes($size);
			$string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
		}
		return $string;
	}

	/**
	 * 生成更加真实的随机字节
	 *
	 * @param  int  $length
	 * @return string
	 */
	public static function randomBytes($length = 16) {
		if (PHP_MAJOR_VERSION >= 7 || defined('RANDOM_COMPAT_READ_BUFFER')) {
			return random_bytes($length);
		} elseif (function_exists('openssl_random_pseudo_bytes')) {
			$bytes = openssl_random_pseudo_bytes($length, $strong);
			if ($bytes === false || $strong === false) {
				Error::out('Unable to generate random string.', __FILE__, __LINE__);
			}
			return $bytes;
		} else {
			Error::out('OpenSSL extension or paragonie/random_compat is required for PHP 5 users.', __FILE__, __LINE__);
		}
		return null;
	}

	/**
	 * 生成简单的随机字符串
	 * @param  int  $length
	 * @return string
	 */
	public static function quickRandom($length = 16) {
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
	}

	/**
	 * 字节长度
	 * @param string $string
	 */
	public static function byteLength($string)
	{
		return mb_strlen($string, '8bit');
	}
	
	public static function filterHtml($content) {
		return preg_replace('/<(.*?)>/', '', htmlspecialchars_decode($content));
	}
	
	/**
	 * 截取字符串为数组，补充explode函数，不建议过长数组
	 */
	public static function explode($str, $link = ' ', $num = 1, $default = null) {
		$arr = explode($link, $str, $num);
		for ($i = 0 ; $i < $num ; $i ++) {
			if (!isset($arr[$i]) || $arr[$i] === '') {
				if (is_array($default)) {
					$arr[$i] = $default[$i];
				} else {
					$arr[$i] = $default;
				}
			}
		}
		return $arr;
	}
	
	/**
	 * 判断字符串是否以$needles开头
	 * @param string $haystack
	 * @param string|array $needles
	 */
	public static function startsWith($haystack, $needles) {
		foreach ((array) $needles as $needle) {
			if ($needle != '' && strpos($haystack, $needle) === 0) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 首字符替换
	 * @param string $search
	 * @param string $arg
	 * @param string $replace
	 */
	public static function firstReplace($arg, $search, $replace = null) {
		return preg_replace('/^'.$search.'/', $replace, $arg, 1);
	}
	
	public static function abslength($str) {
		if (empty($str)) {
			return 0;
		}
		if (function_exists('mb_strlen')) {
			return mb_strlen($str,'utf-8');
		} else {
			preg_match_all("/./u", $str, $ar);
			return count($ar[0]);
		}
	}
	
	public static function utf8_substr($str, $start = 0, $length = 0) {
		if (empty($str)) {
			return false;
		}
		if (function_exists('mb_substr')) {
			if(func_num_args() >= 3) {
				$end = func_get_arg(2);
				return mb_substr($str,$start,$end,'utf-8');
			} else {
				mb_internal_encoding("UTF-8");
				return mb_substr($str,$start);
			}
	
		} else {
			$null = "";
			preg_match_all("/./u", $str, $ar);
			if (func_num_args() >= 3) {
				$end = func_get_arg(2);
				return join($null, array_slice($ar[0], $start, $end));
			} else {
				return join($null, array_slice($ar[0], $start));
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
	
	public static function csubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
		if (function_exists("mb_substr")) {
			if (mb_strlen($str, $charset) <= $length) return $str;
			$slice = mb_substr($str, $start, $length, $charset);
		} else {
			$re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($re[$charset], $str, $match);
			if (count($match[0]) <= $length) return $str;
			$slice = join("", array_slice($match[0], $start, $length));
		}
		if ($suffix) return $slice."…";
		return $slice;
	}
	
	//字符串的无乱码截取
	function sub ($str,$len) {
		$string = '';
		for( $i=0; $i < $len; $i++ ){
			if( ord(substr($str, $i,1)) > 0xa0 ){
				$string .= substr($str,$i,3);    //默认采用utf编码，汉字3个字节
				$i=$i+2;
			}else{
				$string .= substr($str,$i,1);
			}
		}
		return $string;
	}
	
	
}