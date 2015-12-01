<?php 
namespace App\Body;
/*
* http 请求信息获取类
* 
* @author Jason
* @time 2015-11.29
*/

final class Request {
	private static $_instance;
	/**
	 * 单例模式
	 * @return \App\Body\Request
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public $posts;
	public $gets;
	public $requests;
	public $cookies;
	public $files;
	public $servers;
	public $input;
	
	public $error = FALSE;
	
	public function __construct() {
		$this->gets     = $this->_clean($_GET);
		$this->posts    = $this->_clean($_POST);
		$this->requests = $this->_clean($_REQUEST);
		$this->cookies  = $this->_clean($_COOKIE);
		$this->files    = $this->_clean($_FILES);
		$this->input    = $this->_clean(file_get_contents('php://input'));
	}
	
	/**
	 * 格式化
	 * @param unknown $data
	 */
	private function _clean($data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				unset($data[$key]);
				$data[$this->_clean($key)] = $this->_clean($value);
			}
		} else {
			$data = htmlspecialchars($data, ENT_COMPAT);
		}
	
		return $data;
	}
	
	public function get($name = null, $default = null) {
		if ($name === null) {
			return $this->gets;
		}
		return OArray::getVal($name, $this->gets, $default);
	}
	
	public function post($name = null, $default = null) {
		if ($name === null) {
			return $this->posts;
		}
		return OArray::getVal($name, $this->posts , $default);
	}
	
	public function file($name = null, $default = null) {
		if ($name === null) {
			return $this->files;
		}
		return OArray::getVal($name, $this->files , $default);
	}
	
	public function request($name = null, $default = null) {
		if ($name === null) {
			return $this->requests;
		}
		return OArray::getVal($name, $this->requests , $default);
	}
	
	public function input($name = null, $default = null) {
		if ($name === null) {
			return $this->input;
		}
		return OArray::getVal($name, $this->input , $default);
	}
	
	public function server($name = null, $default = null) {
		if ($name === null) {
			return $_SERVER;
		}
		return OArray::getVal($name, $_SERVER, $default);
	}
	
	/**
	 * 获取真实IP
	 *
	 * @access globe
	 *
	 * @return string IP,
	 */
	public static function ip() {
		$realip  = '';
		$unknown = 'unknown';
		if (isset($_SERVER)) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
				$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
				foreach ($arr as $ip) {
					$ip = trim($ip);
					if ($ip != 'unknown') {
						$realip = $ip;
						break;
					}
				}
			} else if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)) {
				$realip = $_SERVER['HTTP_CLIENT_IP'];
			} else if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
				$realip = $_SERVER['REMOTE_ADDR'];
			} else {
				$realip = $unknown;
			}
		} else {
			if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)) {
				$realip = getenv("HTTP_X_FORWARDED_FOR");
			} else if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)) {
				$realip = getenv("HTTP_CLIENT_IP");
			} else if (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)) {
				$realip = getenv("REMOTE_ADDR");
			} else {
				$realip = $unknown;
			}
		}
		$realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;
		return $realip;
	}
	
	public function getMethod() {
		if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
			return strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
		} else {
			return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
		}
	}
	
	public function isCli() {
		if (isset($_SERVER['argv'])) {
			return TRUE;
		}
		return FALSE;
	}
	
	public function isGet() {
		return $this->getMethod() === 'GET';
	}
	
	public function isOptions() {
		return $this->getMethod() === 'OPTIONS';
	}
	
	public function isHead() {
		return $this->getMethod() === 'HEAD';
	}
	
	public function isPost() {
		return $this->getMethod() === 'POST';
	}
	
	public function isDelete() {
		return $this->getMethod() === 'DELETE';
	}
	
	public function isPut() {
		return $this->getMethod() === 'PUT';
	}
	
	public function isPatch() {
		return $this->getMethod() === 'PATCH';
	}
	
	public function isAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}
	
	public function isPjax() {
		return $this->isAjax() && !empty($_SERVER['HTTP_X_PJAX']);
	}
	
	public function isFlash() {
		return isset($_SERVER['HTTP_USER_AGENT']) &&
		(stripos($_SERVER['HTTP_USER_AGENT'], 'Shockwave') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'Flash') !== false);
	}
	
	private function safeCheck() {
	
	}
}