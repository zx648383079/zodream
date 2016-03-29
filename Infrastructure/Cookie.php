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
	 * @param string $key
	 * @param string $default
	 */
	public static function get($key, $default = NULL) {
		 return Request::getInstance()->cookie($key, $default);
	}
	
	/**
	 * set cookie
	 * @param string $name 名称
	 * @param string $value 值
	 * @param number $expire 有效期
	 * @param string $path 服务器路径
	 * @param string $domain 域名
	 * @param boolean $secure 是否通过安全的 HTTPS 连接来传输 cookie。
	 * @param boolean $httpOnly 是否只通过http协议 不允许js等脚本进入，防止xss
	 */
	public static function set($name, $value = '', $expire = 0, $path = null, $domain = null, $secure = FALSE, $httpOnly = FALSE) {
		setcookie($name, $value, time() + $expire, $path, $domain, $secure, $httpOnly);
	}
	
	/**
	 * 删除
	 * @param string $name 名称
	 */
	public static function delete($name) {
		setcookie($name, '', time() - 3600);
	}
}