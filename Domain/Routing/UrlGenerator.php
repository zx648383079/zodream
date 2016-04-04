<?php
namespace Zodream\Domain\Routing;

use Zodream\Infrastructure\Request;

defined('APP_URL') or define('APP_URL', UrlGenerator::getRoot());
class UrlGenerator {
	/**
	 * 上个页面网址
	 *
	 * @return string|bool 网址
	 */
	public static function referer() {
		return Request::server('HTTP_REFERER');
	}
	
	/**
	 * 产生完整的网址
	 * @param string $file
	 * @param string $extra
	 * @param string $secret
	 * @return string
	 */
	public static function to($file = null, $extra = null, $secret = FALSE) {
		$url = self::toByFile($file);
		if ($extra === null) {
			return $url;
		}
		if (is_array($extra)) {
			return self::setValue($url, $extra);
		}
		if (is_object($extra)) {
			return $extra($url);
		}
		if (strpos($url, '?') === false) {
			return $url.'?'.$extra;
		}
		return self::setValue($url, $extra);
	}
	
	protected static function toByFile($file = null) {
		if (strpos($file, '//') !== false) {
			if (strpos($file, '://') !== false || ltrim($file, '/') === substr($file, 2)) {
				return $file;
			}
			$file = str_replace('//', '/', $file);
		}
		if ($file === null || $file === 0) {
			return self::getRoot(FALSE).ltrim(self::getUri(), '/');
		}
		if ($file === '-' || $file === -1) {
			return self::referer();
		}
		if ($file === '' || $file === '/') {
			return APP_URL;
		}
		if (strpos($file, '.') !== false) {
			return self::toAsset($file);
		}
		return call_user_func(array(RouteConfig::getInstance()->getDriver(), 'to'), $file);
	}
	
	public static function toAsset($file) {
		return self::getRoot(FALSE).ltrim($file, '/');
	}
	
	/**
	 * 获取根网址
	 * 
	 * @param boolean $withScript 是否带执行脚本文件
	 * @return string
	 */
	public static function getRoot($withScript = TRUE) {
		$args = parse_url(self::getUri());
		$root = '';
		$secret = Request::server('HTTPS');
		if (empty($secret) || 'off' === strtolower($secret)) {
			$root = 'http';
		} else {
			$root = 'https';
		}
		$root .= '://'.Request::server('HTTP_HOST');
		$port = Request::server('SERVER_PORT');
		if (!empty($port) && $port != 80) {
			$root .= ':'.$port;
		}
		$root .= '/';
		$self = Request::server('script_name');
		if ($self !== '/index.php' && $withScript) {
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
	
	public static function hasUri($search = null) {
		$url = self::getUriWithoutParam();
		if (is_null($search) && $url == '/') {
			return true;
		}
		if (strpos($url, '/'.trim($search, '/')) !== false) {
			return true;
		}
		return false;
	}

	public static function isUrl($url) {
		return trim(self::getUriWithoutParam(), '/') == trim($url, '/');
	}
	
	/**
	 * 获取网址
	 *
	 * @return string 真实显示的网址
	 */
	public static function getUri() {
		$uri         = '';
		$requestUri = Request::server('REQUEST_URI');
		if (!is_null($requestUri)) {
			$uri = $requestUri;
		} else {
			$argv = Request::server('argv');
			$self = Request::server('PHP_SELF');
			if (!is_null($argv)) {
				$uri = $self .'?'. $argv[0];
			} else {
				$uri = $self .'?'. Request::server('QUERY_STRING');
			}
		}
		return $uri;
	}

	public static function getUriWithoutParam() {
		return explode('?', self::getUri())[0];
	}
	
	/**
	 * 判断是否SSL协议
	 * @return boolean
	 */
	public static function isSsl()
	{
		if (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
			return true;
		} elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
			return true;
		}
		return false;
	}
	
	/**
	 * 获取host 包括端口
	 */
	public static function getHost() {
		$host = $_SERVER ['HTTP_HOST'];
		if ($_SERVER['SERVER_PORT'] != 80) {
			$host .= ':'.$_SERVER['SERVER_PORT'];
		}
		return $host;
	}
	
	/**
	 * 获取执行脚本的文件    /index.php
	 */
	public static function getScript() {
		return $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']; 
	}
}