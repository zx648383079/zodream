<?php 
namespace Zodream\Infrastructure;
/**
* cookie 读写类
* 
* @author Jason
*/

class Cookie {
	/**
	 * $_COOKIE
	 * @param unknown $key
	 * @param string $default
	 */
	public static function get($key, $default = NULL) {
		Request::getInstance()->cookie($key, $default);
	}
	
	/**
	 * setcookie
	 * @param unknown $name 名称
	 * @param string $value 值
	 * @param number $expire 有效期
	 * @param string $path 服务器路径
	 * @param string $domain 域名
	 * @param boolean $secure 是否通过安全的 HTTPS 连接来传输 cookie。
	 * @param boolean $httponly 是否只通过http协议 不允许js等脚本进入，防止xss
	 */
	public static function set($name, $value = '', $expire = 0, $path = null, $domain = null, $secure = FALSE, $httponly = FALSE) {
		setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}
	
	/**
	 * 删除
	 * @param unknown $name 名称
	 */
	public static function delete($name) {
		self::set($name, '', time() - 3600);
	}
}