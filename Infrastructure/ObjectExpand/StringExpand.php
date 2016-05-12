<?php 
namespace Zodream\Infrastructure\ObjectExpand;

/**
* string 的扩展
* 
* @author Jason
*/
use Zodream\Infrastructure\Error;
class StringExpand {
	
	public static function formatSize($size) { 
		$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB"); 
		if ($size == 0) {  
			return('n/a');  
		} else { 
		return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);  
		} 
	}

	/**
	 * 绑定值
	 * @param string $sql
	 * @param int $location ? 的位置
	 * @param string|int $var 值
	 * @param string $type 值得类型
	 * @return string
	 */
	public static function bindParam(&$sql, $location, $var, $type) {
		switch (strtoupper($type)) {
			//字符串
			default:                    //默认使用字符串类型
			case 'ENUM':
			case 'TEXT':
			case 'STR':
			case 'STRING' :
				$var = "'".addslashes($var)."'";      //加上单引号.SQL语句中字符串插入必须加单引号
				break;
			case 'INTEGER' :
			case 'INT' :
				$var = (int)$var;         //强制转换成int
				break;
			case 'BOOL':
			case 'BOOLEAN':
				$var = $var ? 1 : 0;
				break;
			case 'NULL':
				$var = 'NULL';
				break;
		}
		//寻找问号的位置
		for ($i=1, $pos = 0; $i<= $location; $i++) {
			$pos = strpos($sql, '?', $pos+1);
		}
		//替换问号
		$sql = substr($sql, 0, $pos) . $var . substr($sql, $pos + 1);
	}

	/**
	 * 压缩html，
	 * @param string $arg
	 * @param bool $all 如果包含js请用false
	 * @return string
	 */
	public static function compress($arg, $all = true) {
		if (!$all) {
			return preg_replace(
				'/>\s+</',
				'><',
				preg_replace(
					"/>\s+\r\n/",
					'>', $arg));
		}
		return ltrim(rtrim(preg_replace(
			array('/> *([^ ]*) *</',
				'//',
				'#/\*[^*]*\*/#',
				"/\r\n/",
				"/\n/",
				"/\t/",
				'/>[ ]+</'
			),
			array(
				'>\\1<',
				'',
				'',
				'',
				'',
				'',
				'><'
			), $arg)));
	}

	/**
	 * 拓展str_repeat 重复字符并用字符连接
	 * @param string $str
	 * @param integer $count
	 * @param string $line
	 * @return string
	 */
	public static function repeat($str, $count, $line = ',') {
		return substr(str_repeat($str.$line, $count), 0, - strlen($line));
	}

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
	 * 生成随机数字字符串
	 * @param $length
	 * @return string
	 */
	public static function randomNumber($length = 6) {
		return sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
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
	 * 合并多个获取完整的路径
	 * @param string $baseDir
	 * @param string $other
	 * @return string
	 */
	public static function getFile($baseDir, $other) {
		return preg_replace('#[\\/]{2,}#', '/', implode('/', func_get_args()));
	}

	/**
	 * 替换url中的参数
	 *
	 * @param string $url
	 * @param string|array $key
	 * @param null|string $value
	 * @return string 合并后的值
	 */
	public static function urlBindValue($url, $key , $value = null) {
		$arr = explode('?', $url, 2);
		$arr = str_replace('&amp;', '&', $arr);      //解决 & 被转义
		$data = array();
		if (count($arr) > 1) {
			parse_str($arr[1], $data);
		}
		if (!is_null($value)) {
			$data[$key] = $value;
		} else {
			if (is_array($key)) {
				$data = array_merge($data, $key);
			} else if (is_string($key)) {
				$keys = array();
				parse_str($key, $keys);
				$data = array_merge($data, $keys);
			}
		}
		return $arr[0].'?'.http_build_query($data);
	}

	/**
	 * 获取两个路径的相对路径
	 * @param string $a1
	 * @param string $b1
	 * @return string
	 */
	public static function getRelationPath($a1, $b1) {
		$a1 = explode('/', ltrim($a1, '/'));
		$b1 = explode('/', ltrim($b1, '/'));
		for($i = 0; isset($b1[$i], $a1[$i]); $i++){
			if($a1[$i] == $b1[$i]) $a1[$i] = "..";
			else break;
		}
		return implode("/", $a1);
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
	 * @return integer
	 */
	public static function byteLength($string) {
		return mb_strlen($string, '8bit');
	}

	/**
	 * 过滤html元素
	 * @param string $content
	 * @return string
	 */
	public static function filterHtml($content) {
		return preg_replace('/<(.*?)>/', '', htmlspecialchars_decode($content));
	}

	/**
	 * 截取字符串为数组，补充explode函数，不建议过长数组
	 * @param $str
	 * @param string $link
	 * @param int $num
	 * @param array|string $default 不存在或为''时使用
	 * @return array
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
	 * @return bool
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
	 * @return string
	 */
	public static function firstReplace($arg, $search, $replace = null) {
		return preg_replace('/^'.$search.'/', $replace, $arg, 1);
	}

	public static function lastReplace($arg, $search, $replace = null) {
		return preg_replace('/'.$search.'$/', $replace, $arg, 1);
	}

	/**
	 * UTF8字符串的长度
	 * @param string $str
	 * @return int
	 */
	public static function absLength($str) {
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

	/**
	 * UTF8字符串截取
	 * @param string $str
	 * @param int $start
	 * @param int $length
	 * @return bool|string
	 */
	public static function subString($str, $start = 0, $length = 0) {
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
	
	public static function subStr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
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

	/**
	 * 字符串的无乱码截取
	 * @param string $str
	 * @param int $len
	 * @return string
	 */
	function sub($str,$len) {
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