<?php 
namespace Zodream\Infrastructure;
/**
* http 请求信息获取类
* 
* @author Jason
*/
use Zodream\Infrastructure\Traits\SingletonPattern;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;

final class Request {
	use SingletonPattern;
	
	private $_posts;
	private $_gets;
	private $_requests;
	private $_cookies;
	private $_files;
	private $_servers;
	private $_input;
	
	public $error = FALSE;
	
	public function __construct() {
		
	}
	
	/**
	 * 格式化
	 * @param array|string $data
	 */
	private function _clean($data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				unset($data[$key]);
				$data[strtolower($this->_clean($key))] = $this->_clean($value);
			}
		} else {
			$data = htmlspecialchars($data, ENT_COMPAT);
		}
	
		return $data;
	}
	
	/**
	 * $_GET
	 * @param string $name
	 * @param string $default
	 * @return array|string
	 */
	public function get($name = null, $default = null) {
		if (empty($this->_gets)) {
			$this->_gets = $this->_clean($_GET);
		}
		return $this->_getValue($name, $this->_gets, $default);
	}
	
	/**
	 * 获取值得总方法
	 * @param string $name
	 * @param array $args
	 * @param string $default
	 */
	private function _getValue($name, $args, $default = null) {
		if ($name === null) {
			return $args;
		}
		return ArrayExpand::getVal(strtolower($name), $args, $default);
	}
	
	/**
	 * $_POST
	 * @param string $name
	 * @param string $default
	 */
	public function post($name = null, $default = null) {
		if (empty($this->_posts)) {
			$this->_posts = $this->_clean($_POST);
		}
		return $this->_getValue($name, $this->_posts , $default);
	}
	
	/**
	 * $_FILES
	 * @param string $name
	 * @param string $default
	 */
	public function file($name = null, $default = null) {
		if (empty($this->_files)) {
			$this->_files = $this->_clean($_FILES);
		}
		return $this->_getValue($name, $this->_files , $default);
	}
	
	/**
	 * $_REQUEST
	 * @param string $name
	 * @param string $default
	 */
	public function request($name = null, $default = null) {
		if (empty($this->_requests)) {
			$this->_requests = $this->_clean($_REQUEST);
		}
		return $this->_getValue($name, $this->_requests , $default);
	}
	
	/**
	 * $_COOKIE
	 * @param string $name
	 * @param string $default
	 */
	public function cookie($name = null, $default = null) {
		if (empty($this->_cookies)) {
			$this->_cookies = $this->_clean($_COOKIE);
		}
		return $this->_getValue($name, $this->_cookies , $default);
	}
	
	/**
	 * PHP://INPUT
	 * @param string $name
	 * @param string $default
	 */
	public function input($name = null, $default = null) {
		if (empty($this->_input)) {
			$this->_input = $this->_clean(file_get_contents('php://input'));
		}
		return $this->_getValue($name, $this->_input , $default);
	}
	
	/**
	 * $_SERVER
	 * @param string $name
	 * @param string $default
	 */
	public function server($name = null, $default = null) {
		if (empty($this->_servers)) {
			$this->_servers = $this->_clean($_SERVER);
		}
		return $this->_getValue($name, $this->_servers, $default);
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
}