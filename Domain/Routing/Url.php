<?php
namespace Zodream\Domain\Routing;

/**
 * url生成
 */
use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

defined('APP_URL') or define('APP_URL', Url::getRoot());
class Url {
	/**
	 * 上个页面网址
	 *
	 * @return string|bool 网址
	 */
	public static function referrer() {
		return Request::server('HTTP_REFERER');
	}

	/**
	 * 产生完整的网址
	 * @param string $file
	 * @param array|string|\Closure $extra
	 * @return string
	 */
	public static function to($file = null, $extra = null) {
		if (is_array($file)) {
			$files = $file;
			foreach ($files as $key => $item) {
				if (is_integer($key)) {
					$file = $item;
					continue;
				}
				$extra[$key] = $item;
			}
			unset($files);
		}
		if ($file === '#' || strpos($file, 'javascript:') != false) {
			return $file;
		}
		if (strpos($file, '?') !== false) {
			$args = explode('?', $file, 2);
			$args[0] = self::toByFile($args[0]);
			$url = implode('?', $args);
		} else {
			$url = self::toByFile($file);
		}
		return self::addParam($url, $extra);
	}

	/**
	 * 给url 添加值
	 * @param string $url
	 * @param array|string|\Closure $extra
	 * @return string
	 */
	protected static function addParam($url, $extra = null) {
		if (empty($extra)) {
			return $url;
		}
		if (is_array($extra)) {
			return StringExpand::urlBindValue($url, $extra);
		}
		if (is_object($extra)) {
			return $extra($url);
		}
		if (strpos($url, '?') === false) {
			return $url.'?'.$extra;
		}
		return StringExpand::urlBindValue($url, $extra);
	}

	/**
	 * 根据网址自动补充完整
	 * @param string $file
	 * @return string
	 */
	protected static function toByFile($file = null) {
		if ($file === null || 0 === $file || '0' === $file) {
			return self::getRoot(FALSE).ltrim(self::getUri(), '/');
		}
		if ($file === '-' || -1 == $file) {
			return self::referrer();
		}
		if (strpos($file, '//') !== false) {
			if (strpos($file, '://') !== false || ltrim($file, '/') === substr($file, 2)) {
				return $file;
			}
			$file = str_replace('//', '/', $file);
		}
		if ($file === '' || $file === '/') {
			return APP_URL;
		}
		if (strpos($file, '.') !== false) {
			return self::toAsset($file);
		}
		return Factory::router()->to($file);
	}

	/**
	 * 获取物理路径
	 * @param string $file
	 * @return string
	 */
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
		$root = (self::isSsl() ? 'https' : 'http'). '://'.self::getHost() . '/';
		$self = Request::server('script_name');
		if ($self !== '/index.php' && $withScript) {
			$root .= ltrim($self, '/');
		}
		return $root;
	}

	/**
	 * 获取host 包括域名和端口 80 隐藏
	 * @return string
	 */
	public static function getHost() {
		$host = Request::server('HTTP_X_FORWARDED_HOST'); // 防止通过局域网代理取得ip值
		if (!empty($host)) {
			return $host;
		}
		$host = Request::server('HTTP_HOST');
		if (!empty($host)) {
			return $host;
		}
		$host = Request::server('SERVER_NAME');
		$port = Request::server('SERVER_PORT');
		if (!empty($port) && $port != 80) {
			$host .= ':'.$port;
		}
		return $host;
	}

	/**判断是否带url段
	 * @param string $search
	 * @return bool
	 */
	public static function hasUri($search = null) {
		$url = self::getUriWithoutParam();
		if (is_null($search) && $url == '/') {
			return true;
		}
		return strpos($url, '/'.trim($search, '/')) !== false;
	}

	/**
	 * 判断是否是url
	 * @param string $url
	 * @return bool
	 */
	public static function isUrl($url) {
		return trim(self::getUriWithoutParam(), '/') == trim($url, '/');
	}
	
	/**
	 * 获取网址
	 *
	 * @return string 真实显示的网址
	 */
	public static function getUri() {
		$uri = Request::server('REQUEST_URI');
		if (!is_null($uri)) {
			return $uri;
		}
		$argv = Request::server('argv');
		$self = Request::server('PHP_SELF');
		if (!is_null($argv)) {
			unset($argv[0]);
			return $self .'?'.implode('&', $argv);
		}
		return $self .'?'. Request::server('QUERY_STRING');
	}

	public static function getUriWithoutParam() {
		return explode('?', self::getUri())[0];
	}
	
	/**
	 * 判断是否SSL协议
	 * @return boolean
	 */
	public static function isSsl() {
		$https = Request::server('HTTPS');
		if ('1' == $https || 'on' == strtolower($https)) {
			return true;
		}
		return Request::server('SERVER_PORT') == 443;
	}
	
	/**
	 * 获取执行脚本的文件    /index.php
	 */
	public static function getScript() {
		return Request::server('PHP_SELF') ?: Request::server('SCRIPT_NAME'); 
	}
}