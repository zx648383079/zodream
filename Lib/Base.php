<?php
namespace App\Lib;	
/*****************************************************
*全局方法
*
*
*
********************************************************/
use App\Lib\Object\OArray;
use App\Lib\Helper\HUrl;
use App\Lib\Web\WRequest;
use App\Lib\Role\RComma;
use App\Lib\Html\HScript;
use App\Lib\Html\HView;

ini_set("session.cookie_httponly", 1);
 
defined("DEBUG")   or define("DEBUG", false);
defined("APP_DIR") or define("APP_DIR", dirname(dirname(__FILE__)));
defined("APP_API") or define('APP_API', isset($_GET['api']) ? TRUE : FALSE);    //是否是API模式

class Base {
	
	public static $request;
	
	private static $root;
	
	public static function main($arg = 'app') {
		set_error_handler(array('app', 'error'));          //自定义错误输出
		register_shutdown_function(array('app', 'out'));   //程序结束时输出
		//Lang::setLang();                                 //加载语言包 
		self::$root    = $arg;
		self::$request = new WRequest();
		date_default_timezone_set('Etc/GMT-8');            //这里设置了时区
		Route::load($arg);
	}
	/**
	* 获取配置文件
	*
	* @access globe
	*
	* @param string|null $key 要获取的配置名
	* @param $default 返回默认值
	* @return array,
	*/
	public static function config($key = null, $default = null) {
		$configs = require(APP_DIR.(empty(self::$root) ? '' : '/'.self::$root).'/config/config.php');
		if (!empty($key)) {
			$configs = OArray::getChild($key, $configs, $default);
		}
		return $configs;
	}

	/**
	 * 判断权限是否符合
	 *
	 * @access globe
	 *
	 * @param int $role 权限编号
	 *
	 * @return string
	 */
	public static function role($role) {
		if (Auth::guest()) {
			return empty($role);
		} else {
			return RComma::judge($role, Auth::user()->role()->roles);
		}
	}
	
	/**
	 * 产生完整的网址
	 *
	 * @access globe
	 *
	 * @param string $file 本站链接
	 * @param bool $echo 是否输出
	 *
	 * @return string
	 */
	public static function url($file = null, $echo = TRUE) {
		$url = HUrl::to($file);
		if ($echo) {
			echo $url;
		} else {
			return $url;
		}
	}	

	/**
	* 主要是加载 js、css 文件
	*
	* @access globe
	*
	*
	* @return null
	*/
	public static function jcs() {
		$files = OArray::sort(func_get_args());
		HScript::make($files);
	}
	
	/**
	* 判断是否存在并输出
	*
	*
	* @param string $name 要显示的
	* @param string|function $text 默认值.
	*/
	public static function ech($name, $text = '') {
		$result = OArray::getChild($name, self::$data, is_object($text) ? '' : $text);
		if (is_object($text)) {
			$text($result);
		} else {
			echo OArray::tostring($result);
		}
	}
	
	/**
	* 判断是否存在并返回
	*
	*
	* @param string $name 要返回的
	* @param string|function $text 默认值.
	*/
	public static function ret($name, $text = '') {
		$result = OArray::getChild($name, self::$data, is_object($text) ? '' : $text);
		if (is_object($text)) {
			$text($result);
		} else {
			return $result;
		}
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
			session_save_path(APP_DIR.'/tmp');
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
	
	/**
	* 跳转页面
	*
	* @access globe
	*
	* @param string $url 要跳转的网址
	* @param int $time 停顿的时间
	* @param string $msg 显示的消息.
	* @param string $code 显示的代码标志.
	*/
	public static function redirect($urls, $time = 0, $msg = '', $code = '') {
		$url = '';
		if (is_array($urls)) {
			foreach ($urls as $value) {
				$url .= HUrl::to($value);
			}
		} else {
			$url = HUrl::to($urls);
		}
		
		if (empty($msg)) {
			$msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
		}
		if (!headers_sent()) {
			if (0 === $time) {
				header('Location: ' . $url);
			} else {
				header("refresh:{$time};url={$url}");
			}
		} else {
			$str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			self::$data['meta'] = $str;
		}
		self::$data['title'] = "出错了！";
		self::$data['code']  = $code;
		self::$data['error'] = $msg;
		self::extend('404');
		exit();
	}
	
	//要传的值
	public static $data;
	//额外的值
	public static $extra;
	/**
	* 包含文件
	*
	* @access globe
	*
	* @param string $names 路径加文件名
	* @param string|null $param 要传的额外的值
	* @param string|null $replace 额外值是否替换
	* @,
	*/
	public static function extend($names, $param = null, $replace = null) {
		if ($replace == '+') {
			self::$extra[] = $param;
		} else {
			self::$extra = $param;
		}
		foreach (OArray::to( $names , '.') as $value) {
			include(HView::make($value));
		}
	}


	/**
	 * @param array|string $info 调试的信息
     */
	public static function out($info = null) {
		if (defined('DEBUG') && DEBUG) {
			$error = error_get_last();
			if (!empty($error) || !empty($info)) {
				if (!empty($error) || !empty($info)) {
					header( 'Content-Type:text/html;charset=utf-8' );
					echo "<div style=\"text-align:center;color:red;font-weight:700;font-size:20px\">";
					empty($error) ? '':printf("错误提示：%s！在%s中第%u行。", $error['message'], $error['file'], $error['line']);
					empty($info) ? '' : var_dump($info);
					echo '</div>';
				}
			}
		}
	}

	/**
	 * 调试时的输出错误信息
	 *
	 * @access globe
	 *
	 * @param int $errno 包含了错误的级别
	 * @param string $errstr 包含了错误的信息
	 * @param string $errfile  包含了发生错误的文件名
	 * @param int $errline 包含了错误发生的行号
	 * @param array $errcontext 是一个指向错误发生时活动符号表的 array
	 * @internal param array|null|string $info 信息
	 */
	public static function error($errno, $errstr, $errfile, $errline) {
		header( 'Content-Type:text/html;charset=utf-8' );
		if (defined('DEBUG') && DEBUG) {
			self::$data['error'] = '错误级别：'.$errno.'错误的信息：'.$errstr.'<br>发生在 '.$errfile.' 第 '.$errline.' 行！';
		} else {
			self::$data['error'] = '出错了！';
		}
		self::extend('404');
		die();
	}
	
	/**
	* 写日志记录
	*
	* @access globe
	*
	* @param string|array $logs 信息
	*/
	public static function writeLog($logs) {
		$log = '';
		if (is_array($logs)) {
			foreach ($logs as $k => $r) {
				$log .= "{$k}='{$r}',";
			}
		} else {
			$log = $logs;
		}
		$logFile = date('Y-m-d').'.txt';
		$log     = date('Y-m-d H:i:s').' >>> '.$log."\r\n";
		file_put_contents($logFile,$log, FILE_APPEND );
	}

}