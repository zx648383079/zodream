<?php 
namespace Zodream\Domain;
/**
 * 验证
 *
 * @author Jason
 */

class Validate {

	protected $error = array();
	
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
				
			if (isset($request[$key]) && !$this->isNull($request[$key])) {
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
				return $this->isNum($value) ? TRUE : ' is not number';
				break;
			case 'float':
				return $this->isNum($value , 'float') ? TRUE : ' is not float';
				break;
			case 'email':
				return $this->isEmail($value) ? TRUE : ' is not email';
				break;
			case 'phone':
				return $this->isMobile($value) ? TRUE : ' is not phone';
				break;
			case 'url':
				return $this->isUrl($value) ? TRUE : ' is not url';
				break;
			case 'datetime':
				return $this->isDateTime($value) ? TRUE : ' is not datetime';
				break;
			case 'length':
				$len = explode('-', $arr[1]);
				return $this->length($value, 3, intval($len[0]), intval($len[1])) ? TRUE : '\'s length is not between '.$len[0].' and '.$len[1];
				break;
			case 'min':
				return $this->length($value, 1, intval($arr[1])) ? TRUE : ' min length is '.$arr[1];
				break;
			case 'max':
				return $this->length($value, 2, 0, intval($arr[1])) ? TRUE : ' max length is '.$arr[1];
				break;
			case 'regular':
				return $this->regular($value, $arr[1]) ? TRUE : ' is not match';
				break;
			case 'confirm':
				return $this->confirm($value, $arr[1]) ? TRUE : ' is not the same as '.$arr[1];
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
				return $this->unique($tables[0], $column, $value) ? TRUE : ' is exist.';
				break;
			default:
				return TRUE;
				break;
		}
	}
	
	private function isNull($value) {
		return ($value === null || $value === '');
	}
	
	private function isDateTime($value) {
		return strtotime($value);
	}
	
	/**
	 * 对比确认
	 *
	 * @param string $table  不带前缀的表名
	 * @param string $value  要验证的列
	 * @param string $value  要验证的值
	 * @return boolean
	 */
	private function unique($table, $column, $value) {
		$db  = new Model();
		$data = $db->findByHelper(array (
				'select' => 'COUNT(*) as num',
				'from'   => $table,
				'where'  => "$column = '$value'"
		), false);
		if (empty($data) || $data[0]->num != '0') {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * 对比确认
	 *
	 * @param string $value  要验证的值
	 * @param string $key 对比值得关键字
	 * @return boolean
	 */
	private function confirm($value, $key) {
		if (!isset($this->request[$key])) {
			return false;
		}
		return ($value === $this->request[$key]);
	}
	
	/**
	 * 数字验证
	 *
	 * @param $str
	 * @param string $flag int是否是整数，float是否是浮点型
	 * @return boolean
	 */
	private function isNum($str, $flag = 'int') {
		if (strtolower($flag) == 'int') {
			return ((string)(int)$str === (string)$str) ? true : false;
		} else {
			return ((string)(float)$str === (string)$str) ? true : false;
		}
	}
	
	/**
	 * 验证是否是带小数的浮点型
	 * @param $value
	 * @return bool
	 */
	private function isFloat($value) {
		return is_float($value) || ( (float) $value > (int) $value || strlen($value) != strlen( (int) $value) ) && (int) $value != 0 ;
	}
	
	/**
	 * 邮箱验证
	 * @param $str
	 * @return boolean
	 */
	private function isEmail($str) {
		return preg_match("/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i",$str) ? true : false;
	}
	//手机号码验证
	/**
	 * @param $str
	 * @return boolean
	 */
	private function isMobile($str) {
		$exp = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';
		if (preg_match($exp,$str)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * URL验证，纯网址格式，不支持IP验证
	 * @param $str
	 * @return boolean
	 */
	private function isUrl($str) {
		return preg_match('#(http|https|ftp|ftps)://([w-]+.)+[w-]+(/[w-./?%&=]*)?#i',$str) ? true : false;
	}
	
	/**
	 * 验证长度
	 * @param string $str
	 * @param int $type(方式，默认min <= $str <= max)
	 * @param int $min,最小值;
	 * @param int $max,最大值;
	 * @param string $charset 字符
	 * @return boolean
	 */
	private function length($str, $type = 3, $min = 0, $max = 0, $charset = 'utf-8') {
		$len = mb_strlen($str,$charset);
		switch ($type) {
			case 1: //只匹配最小值
				return ($len >= $min) ? true : false;
				break;
			case 2: //只匹配最大值
				return ($max >= $len) ? true : false;
				break;
			default: //min <= $str <= max
				return (($min <= $len) && ($len <= $max)) ? true : false;
		}
	}
	
	/**
	 * 正则验证
	 * @param: string $str
	 * @param: string $patten 正则字符串
	 * @return boolean
	 */
	private function regular($str, $patten) {
		return preg_match($str, $patten) ? TRUE : false;
	}

	/**
	 * 设置error
	 * @param string $key
	 * @param string $error
	 */
	public function setError($key, $error) {
		$error = $key. $error;
		if (isset($this->error[$key])) {
			$this->error[$key][] = $error;
		} else {
			$this->error[$key] = array($error);
		}
	}
	/**
	 * @return array 获取错误信息
	 */
	public function getError() {
		return $this->error;
	}
}