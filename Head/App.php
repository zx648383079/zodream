<?php
namespace App\Head;
/**
* 全局方法
* 
* @author Jason
* @time 2015-11.29
*/
defined('VERSION') or define('VERSION', 1.0);
defined('APP_DIR') or define('APP_DIR', dirname(dirname(__FILE__)).'/');

class App {
	/**
	 * 程序启动
	 */
	public static function main() {
		date_default_timezone_set('Etc/GMT-8');            //这里设置了时区
		Route::run();
	}
	
	/**
	 * 操作session 设置和获取值
	 *
	 * @param string $keys 关键字 多层用‘.’ 分割
	 * @param string $value 要设置的值
	 * @return string
	 */
	public static function session($keys, $value = false, $life = '') {
		if (!isset($_SESSION)) {
			session_save_path(dirname(APP_DIR).'/tmp');
			session_start();
		}
	
		if (empty($keys)) {
			session_destroy();
			return;
		}
	
		if (is_bool($value)) {
			return OArray::getChild($keys, $_SESSION);
		} else {
			$arr = explode('.', $keys);
			$str = '$_SESSION';
			foreach ($arr as $val) {
				$str .= "['{$val}']";
			}
			$str .= ' = $value;';
			eval($str);
		}
	}
	
	public static function cookie($key, $value = false, $time = 0) {
		if (is_bool($value)) {
			return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
		} else if ($value === null) {
			setcookie($key);
		} else {
			setcookie($key, $value, $time);
		}
	}
	
	
}