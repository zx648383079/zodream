<?php 
namespace App\Lib\Web;

use App\Lib\Object\OArray;

final class WRequest implements IBase {
	public $posts;
	public $gets;
	public $requests;
	public $cookies;
	public $files;
	public $servers;
	
	public $error = FALSE;
	
	public function __construct() {
		$this->gets     = $this->_clean($_GET);
		$this->posts    = $this->_clean($_POST);
		$this->requests = $this->_clean($_REQUEST);
		$this->cookies  = $this->_clean($_COOKIE);
		$this->files    = $this->_clean($_FILES);
		$this->servers  = $this->_clean($_SERVER);
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
	
	public function delete() {
		
	}
	
	public function put() {
		
	}
    
	public function server($name = null, $default = null) {
		if ($name === null) {
			return $_SERVER;
		}
		return OArray::getVal($name, $_SERVER, $default);
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
	
	public function __get($name) {
		return OArray::getVal($name, array_merge($this->gets, $this->posts), null, '_');
	}
	
	private function safeCheck() {
		
	}
}