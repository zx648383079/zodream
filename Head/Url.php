<?php 
namespace App\Head;
/*
 * url
 *
 * @author Jason
 * @time 2015-12-1
 */
use App\Body\Request;
use App\Body\Config;
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
	public static function to($file = null, $extra = null, $secret = FALSE, $mode = APP_MODE) {
		if (strstr($file, '//')) {
			return $file;
		}
		switch ($file) {
			case '-':
			case -1:
				return self::referer();
				break;
			case null:
			case 0:
				return self::request_uri();
				break;
			case '':
			case '/':
				return APP_URL;
				break;
			default:
				break;
		}
		
		$url = call_user_func(array(Config::getInstance()->get('app.rounte'), 'get'), $file);
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
		return self::to($file, null, false, null);
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
		$uri = '';
		if (isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['REQUEST_URI'];
		} else {
			if (isset( $_SERVER['argv'])) {
				$uri = $_SERVER['REQUEST_URI'];
			} else {
				if (isset($_SERVER['argv'])) {
					$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
				} else {
					$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
				}
			}
		}
		return $uri;
	}
}