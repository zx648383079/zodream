<?php 
namespace Zodream\Head;
/**
 * url
 *
 * @author Jason
 * @time 2015-12-1
 */
use Zodream\Body\Request;
use Zodream\Body\Config;

defined('APP_URL') or define('APP_URL', Url::getRoot());

class Url {
	
	#字符表
	public static $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	
	/**
	 * 生成短链接
	 *
	 * @param $url
	 * @return array
	 */
	public static function short($url) {
		$key     = "alexis";
		$urlhash = md5($key . $url);
		$len     = strlen($urlhash);
	
		//将加密后的串分成4段，每段4字节，对每段进行计算，一共可以生成四组短连接
		for ($i = 0; $i < 4; $i ++) {
			$urlhash_piece = substr($urlhash, $i * $len / 4, $len / 4);
			//将分段的位与0x3fffffff做位与，0x3fffffff表示二进制数的30个1，即30位以后的加密串都归零
			$hex = hexdec($urlhash_piece) & 0x3fffffff; //此处需要用到hexdec()将16进制字符串转为10进制数值型，否则运算会不正常
	
			$short_url = "http://t.cn/";
			//生成6位短连接
			for ($j = 0; $j < 6; $j++) {
				//将得到的值与0x0000003d,3d为61，即charset的坐标最大值
				$short_url .= self::$charset[$hex & 0x0000003d];
				//循环完以后将hex右移5位
				$hex = $hex >> 5;
			}
			$short_url_list[] = $short_url;
		}
		return $short_url_list;
	}
	
	/**
	 * 上个页面网址
	 *
	 * @return string|bool 网址
	 */
	public static function referer() {
		return Request::getInstance()->server('HTTP_REFERER');
	}
	
	/**
	 * 产生完整的网址
	 * @param string $file
	 * @param string $extra
	 * @param string $secret
	 * @param string $mode 是哪种模式 ，文件用null
	 * @return string
	 */
	public static function to($file = null, $extra = null, $secret = FALSE) {
		if (strstr($file, '//')) {
			return $file;
		}
		if ($file === null || $file === 0) {
			return APP_URL.ltrim(self::request_uri(), '/');
		}
		if ($file === '-' || $file === -1) {
			return self::referer();
		}
		if ($file === '' || $file === '/') {
			return APP_URL;
		}
		$url = call_user_func(array(Config::getInstance()->get('route.driver'), 'to'), $file);
		
		if ($extra === null) {
			return $url;
		} else if (is_string($extra)) {
			if (strpos($url, '?') === false) {
				$url .= '?'.$extra;
			} else {
				$url .= '&'.$extra;
			}
		} else if(is_array($extra)) {
			$url = self::setValue($url, $extra);
		}
		return $url;
	}
	
	/**
	 * 文件路径
	 * @param unknown $file
	 * @return string
	 */
	public static function file($file) {
		return self::to($file, null);
	}
	
	/**
	 * 获取根网址
	 */
	public static function getRoot() {
		$args = parse_url(self::request_uri());
		$root = '';
		$secret = Request::getInstance()->server('HTTPS');
		if (empty($secret) || 'off' === strtolower($secret)) {
			$root = 'http';
		} else {
			$root = 'https';
		}
		$root .= '://'.Request::getInstance()->server('HTTP_HOST');
		$port = Request::getInstance()->server('SERVER_PORT');
		if (!empty($port) && $port != 80) {
			$root .= ':'.$port;
		}
		$root .= '/';
		$self = Request::getInstance()->server('PHP_SELF');
		if ($self !== '/index.php') {
			$root .= ltrim($self, '/');
		}
		return $root;
	}
	
	/**
	 * 替换url中的参数
	 *
	 * @return string 真实显示的网址
	 */
	public static function setValue($url, $key , $value = null) {
		$arr  = explode('?', $url, 2);
		$data = array();
		if (count($arr) > 1) {
			parse_str( $arr[1], $data );
		}
		if ($value === null && is_array($key)) {
			$data = array_merge($data, $key);
		} else {
			$data[ $key ] = $value;
		}
		return $arr[0].'?'.http_build_query($data);
	}
	
	/**
	 * 获取网址
	 *
	 * @return string 真实显示的网址
	 */
	private static function request_uri() {
		$uri         = '';
		$request_uri = Request::getInstance()->server('REQUEST_URI');
		if (!is_null($request_uri)) {
			$uri = $request_uri;
		} else {
			$argv = Request::getInstance()->server('argv');
			$self = Request::getInstance()->server('PHP_SELF');
			if (!is_null($argv)) {
				$uri = $self .'?'. $argv[0];
			} else {
				$uri = $self .'?'. Request::getInstance()->server('QUERY_STRING');
			}
		}
		return $uri;
	}
}