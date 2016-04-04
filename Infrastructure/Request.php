<?php 
namespace Zodream\Infrastructure;
/**
* http 请求信息获取类
* 
* @author Jason
*/
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Request\BaseRequest;
defined('APP_SAFE') or define('APP_SAFE', Config::getInstance()->get('app.safe', true));

final class Request {

	private static $_instances = array(
		'cookie' => null,
		'files' => null,
		'get' => null,
		'post' => null,
		'header' => null,
		'input' => null,
		'request' => null,
		'server' => null,
		'other' => null
	);

	/**
	 * @param $name
	 * @return BaseRequest
	 */
	private static function _getInstance($name) {
		$name = strtolower($name);
		if (!array_key_exists($name, self::$_instances)) {
			return null;
		}
		if (self::$_instances[$name] instanceof BaseRequest) {
			return self::$_instances[$name];
		}
		$class = 'Zodream\\Infrastructure\\Request\\'.ucfirst($name);
		return self::$_instances[$name] = new $class;
	}

	/**
	 * $_GET
	 * @param string $name
	 * @param string $default
	 * @return array|string
	 */
	public static function get($name = null, $default = null) {
		return self::_getInstance(__FUNCTION__)->get($name, $default);
	}

	/**
	 * $_POST
	 * @param string $name
	 * @param string $default
	 * @return array|string
	 */
	public static function post($name = null, $default = null) {
		return self::_getInstance(__FUNCTION__)->get($name, $default);
	}

	/**
	 * $_FILES
	 * @param string $name
	 * @param string $default
	 * @return array|string
	 */
	public static function files($name = null, $default = null) {
		return self::_getInstance(__FUNCTION__)->get($name, $default);
	}

	/**
	 * $_REQUEST
	 * @param string $name
	 * @param string $default
	 * @return array|string
	 */
	public static function request($name = null, $default = null) {
		return self::_getInstance(__FUNCTION__)->get($name, $default);
	}

	/**
	 * $_COOKIE
	 * @param string $name
	 * @param string $default
	 * @return array|string
	 */
	public static function cookie($name = null, $default = null) {
		return self::_getInstance(__FUNCTION__)->get($name, $default);
	}
	
	/**
	 * PHP://INPUT
	 */
	public static function input() {
		return self::_getInstance(__FUNCTION__)->get();
	}

	/**
	 * $_SERVER
	 * @param string $name
	 * @param string $default
	 * @return array|string
	 */
	public static function server($name = null, $default = null) {
		return self::_getInstance(__FUNCTION__)->get($name, $default);
	}
	
	public static function header($name = null, $default = null) {
		return self::_getInstance(__FUNCTION__)->get($name, $default);
	}

	/**
	 * 一些手动添加的
	 * @param null $name
	 * @param null $default
	 * @return array|string
	 */
	public static function Other($name = null, $default = null) {
		return self::_getInstance(__FUNCTION__)->get($name, $default);
	}

	
	public static function isCli() {
		return !empty(self::server('argv'));
	}


	public static function ip() {
		return self::Other(__FUNCTION__);
	}
	
	public static function isGet() {
		return self::Other('method') === 'GET';
	}
	
	public static function isOptions() {
		return self::Other('method') === 'OPTIONS';
	}
	
	public static function isHead() {
		return self::Other('method') === 'HEAD';
	}
	
	public static function isPost() {
		return self::Other('method') === 'POST';
	}
	
	public static function isDelete() {
		return self::Other('method') === 'DELETE';
	}
	
	public static function isPut() {
		return self::Other('method') === 'PUT';
	}
	
	public static function isPatch() {
		return self::Other('method') === 'PATCH';
	}
	
	public static function isAjax() {
		return self::server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
	}
	
	public static function isPjax() {
		return self::isAjax() && !empty(self::server('HTTP_X_PJAX'));
	}
	
	public static function isFlash() {
		$arg = self::server('HTTP_USER_AGENT', '');
		return stripos($arg, 'Shockwave') !== false || stripos($arg, 'Flash') !== false;
	}
}