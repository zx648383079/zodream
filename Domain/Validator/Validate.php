<?php 
namespace Zodream\Domain\Validator;
/**
 * 验证
 *
 * @author Jason
 */
use Zodream\Domain\Model;

class Validate {

	protected $_error = array();
	
	protected $request;
	
	/**
	 * 开始验证
	 *
	 * @param array $request 要验证的数组
	 * @param array $pattern 规则数组
	 * @return boolean
	 */
	public function make(array $request, array $pattern) {
		$success       = true;
		$this->request = $request;
		foreach ($pattern as $key => $val) {
			$arr = explode('|', $val);
			if (isset($request[$key]) && !$this->_isNullOrEmpty($request[$key])) {
				foreach ($arr as $v) {
					$result = $this -> check($key, $v);
					if (!is_bool($result)) {
						$this->setError($key, $result);
						$success = false;
					}
				}
			} else {
				if (in_array('required', $arr)) {
					$this->setError($key, ' is required');
					$success = false;
				}
			}
		}
		return $success;
	}
	
	/**
	 * 验证
	 *
	 * @param string $key 关键字
	 * @param string $patten 规则名
	 * @return boolean|string
	 */
	private function check($key, $patten) {
		$value  = $this->request[$key];
		$arr    = explode(':' , $patten , 2);
		switch (strtolower($arr[0])) {
			case 'required':
				return true;
				break;
			case 'number':
				return $this->_isIntOrFloat($value) ? TRUE : ' is not number';
				break;
			case 'float':
				return $this->_isIntOrFloat($value , false) ? TRUE : ' is not float';
				break;
			case 'email':
				return $this->_isEmail($value) ? TRUE : ' is not email';
				break;
			case 'phone':
				return $this->_isTelephoneNumber($value) ? TRUE : ' is not phone';
				break;
			case 'url':
				return $this->_isUrl($value) ? TRUE : ' is not url';
				break;
			case 'datetime':
				return $this->_isUrl($value) ? TRUE : ' is not datetime';
				break;
			case 'length':
				$len = explode('-', $arr[1]);
				return $this->_isLengthBetweenMinAndMax($value, intval($len[0]), intval($len[1])) ? TRUE : '\'s length is not between '.$len[0].' and '.$len[1];
				break;
			case 'min':
				return $this->_isMinLength($value, intval($arr[1])) ? TRUE : ' min length is '.$arr[1];
				break;
			case 'max':
				return $this->_isMaxLength($value, intval($arr[1])) ? TRUE : ' max length is '.$arr[1];
				break;
			case 'regular':
				return $this->_isrRegularMatch($value, $arr[1]) ? TRUE : ' is not match';
				break;
			case 'confirm':
				return $this->_isConfirmWithOtherKey($value, $arr[1]) ? TRUE : ' is not the same as '.$arr[1];
				break;
			case 'conform':
				return ($value === $arr[1]) ? TRUE : ' is not equal '.$arr[1];
				break;
			case 'unique':
				$tables = explode('.', $arr[1], 2);
				$column  = $key;
				if (!empty($tables[1])) {
					$column = $tables[1];
				}
				return $this->_isUniqueOnTable($tables[0], $column, $value) ? TRUE : ' is exist.';
				break;
			default:
				return TRUE;
				break;
		}
	}

	/**
	 * 判断是null 或 是空白字符
	 * @param string $value
	 * @return bool
	 */
	private function _isNullOrEmpty($value) {
		return $value === null || trim($value) === '';
	}

	/**
	 * 判断能否转换为时间
	 * @param string $value
	 * @return bool
	 */
	private function _isDateTime($value) {
		return strtotime($value) !== false;
	}

	/**
	 * 对比确认
	 * @return bool
	 * @internal param string $table 不带前缀的表名
	 * @internal param string $value 要验证的列
	 * @internal param string $value 要验证的值
	 */
	private function _isUniqueOnTable() {
		return false;
	}
	
	/**
	 * 对比确认
	 *
	 * @param string $value  要验证的值
	 * @param string $key 对比值得关键字
	 * @return boolean
	 */
	private function _isConfirmWithOtherKey($value, $key) {
		if (!isset($this->request[$key])) {
			return false;
		}
		return ($value === $this->request[$key]);
	}

	/**
	 * 数字验证
	 * @param string|int|float $value
	 * @param bool $isInt
	 * @return bool
	 */
	private function _isIntOrFloat($value, $isInt = true) {
		if ($isInt) {
			return (string)(int)$value === (string)$value;
		} else {
			return (string)(float)$value === (string)$value;
		}
	}
	
	/**
	 * 验证是否是带小数的浮点型
	 * @param $value
	 * @return bool
	 */
	private function _isFloat($value) {
		return is_float($value) || ( (float) $value > (int) $value || strlen($value) != strlen( (int) $value) ) && (int) $value != 0 ;
	}
	
	/**
	 * 邮箱验证
	 * @param $value
	 * @return boolean
	 */
	private function _isEmail($value) {
		return preg_match("/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i", $value) === 1;
	}
	//手机号码验证
	/**
	 * @param $value
	 * @return boolean
	 */
	private function _isTelephoneNumber($value) {
		return preg_match( '', $value) === 1;
	}
	
	/**
	 * URL验证，纯网址格式，不支持IP验证
	 * @param $value
	 * @return boolean
	 */
	private function _isUrl($value) {
		return preg_match('#(http|https|ftp|ftps)://([w-]+.)+[w-]+(/[w-./?%&=]*)?#i',$value) === 1;
	}

	/**
	 * 最小长度
	 * @param string $value
	 * @param int $length
	 * @return bool
	 */
	private function _isMinLength($value, $length) {
		return mb_strlen($value, 'utf-8') >= $length;
	}

	/**
	 * 最大长度
	 * @param string $value
	 * @param int $length
	 * @return bool
	 */
	private function _isMaxLength($value, $length) {
		return mb_strlen($value, 'utf-8') <= $length;
	}

	/**
	 * 长度在最小和最大之间
	 * @param string $value
	 * @param int $min
	 * @param int $max
	 * @return bool
	 */
	private function _isLengthBetweenMinAndMax($value, $min, $max) {
		$length = mb_strlen($value, 'utf-8');
		return $length >= $min && $length <= $max;
	}
	
	/**
	 * 正则验证
	 * @param: string $value
	 * @param: string $pattern 正则字符串
	 * @return boolean
	 */
	private function _isrRegularMatch($value, $pattern) {
		return preg_match($value, $pattern) === 1;
	}

	/**
	 * 设置一条error
	 * @param string $key
	 * @param string $filter
	 * @param bool $result
	 * @return bool
	 */
	public function setError($key, $filter, $result = false) {
		if ($result) {
			return $result;
		}
		if (!isset($this->_error[$key])) {
			$this->_error[$key] = array();
		}
		return $this->_error[$key][$filter] = $result;
	}

	/**
	 * 是否有错误
	 * @return bool
	 */
	public function hasError() {
		return !empty($this->_error);
	}
	/**
	 * @return array 获取错误信息
	 */
	public function getError() {
		return $this->_error;
	}
}