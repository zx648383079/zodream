<?php
namespace Zodream\Infrastructure\Url;

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
     * 产生网址
     * @param string $file
     * @param array|string|\Closure $extra
     * @param bool $complete
     * @return string|Uri
     */
	public static function to($file = null, $extra = null, $complete = false) {
	    if ($file instanceof Uri) {
	        $file->addData($extra);
	        return $file;
        }
        if (is_string($file) &&
            ($file === '#' || strpos($file, 'javascript:') != false)) {
            return $file;
        }
        if (is_bool($extra)) {
            $complete = $extra;
            $extra = null;
        }
        $uri = new Uri();
		if (is_array($file)) {
			foreach ($file as $key => $item) {
				if (is_integer($key)) {
					$uri->decode(static::getPath($item));
					continue;
				}
				$uri->addData($key, (string)$item);
			}
			if (empty($uri->getPath())) {
                $uri->decode(static::getPath(null));
            }
		} else {
		    $uri->decode(static::getPath($file));
        }
        if (!empty($extra)) {
            $uri->setData($extra);
        }
        if ($complete && empty($uri->getHost())) {
            $uri->setScheme(self::isSsl() ? 'https' : 'http')
                ->setHost(self::getHost());
        }
        return $uri;
	}

	protected static function getPath($path) {
	    if (empty($path) || $path === '0') {
	        return self::getUri();
        }
        if ($path === -1 || $path === '-1') {
            return static::referrer();
        }
        if (!empty(parse_url($path, PHP_URL_HOST))) {
            return $path;
        }
        if (strpos($path, '//') !== false) {
            $path = preg_replace('#/+#', '/', $path);
        }
        if (strpos($path, '/') === 0) {
            return $path;
        }
        $name = Request::server('script_name');
        if ($name === '/index.php') {
            return '/'.$path;
        }
        return $name.'/'.$path;
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