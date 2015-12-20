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
			if (strstr($file, '://') || ltrim($file, '/') === substr($file, 2)) {
				return $file;
			}
			$file = str_replace('//', '/', $file);
		}
		if ($file === null || $file === 0) {
			return APP_URL.ltrim(self::getUri(), '/');
		}
		if ($file === '-' || $file === -1) {
			return self::referer();
		}
		if ($file === '' || $file === '/') {
			return APP_URL;
		}
		if (strpos($file, '.') !== false) {
			$url = APP_URL.ltrim($file, '/');
		} else {
			$url = call_user_func(array(RouteConfig::getInstance()->getDriver(), 'to'), $file);
		}
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
	 * 获取根网址
	 */
	public static function getRoot() {
		$args = parse_url(self::getUri());
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
		$self = Request::getInstance()->server('script_name');
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
	public static function getUri() {
		$uri         = '';
		$requestUri = Request::getInstance()->server('REQUEST_URI');
		if (!is_null($requestUri)) {
			$uri = $requestUri;
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