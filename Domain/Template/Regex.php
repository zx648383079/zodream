<?php
namespace Zodream\Domain\Template;

use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
class Regex extends MagicObject {
	
	public function function_name($param = array()) {
		$this->set($param);
	}

	/**
	 * 取得的值
	 * array (size=6)
	 * 0 => string '{extend:hh}' (length=11)
	 * 1 => string '' (length=0)
	 * 2 => string '' (length=0)
	 * 3 => string '' (length=0)
	 * 4 => string 'extend' (length=6)
	 * 5 => string 'hh' (length=2)
	 *
	 * array (size=8)
	 * 0 => string '{a?b:c}' (length=7)
	 * 1 => string '' (length=0)
	 * 2 => string '' (length=0)
	 * 3 => string '' (length=0)
	 * 4 => string '' (length=0)
	 * 5 => string '' (length=0)
	 * 6 => string 'a' (length=1)
	 * 7 => string 'b:c' (length=3)
	 *
	 * array (size=10)
	 * 0 => string '{c,v=d}' (length=7)
	 * 1 => string '' (length=0)
	 * 2 => string '' (length=0)
	 * 3 => string '' (length=0)
	 * 4 => string '' (length=0)
	 * 5 => string '' (length=0)
	 * 6 => string '' (length=0)
	 * 7 => string '' (length=0)
	 * 8 => string 'c,v' (length=3)
	 * 9 => string 'd' (length=1)
	 *
	 * array (size=11)
	 * 0 => string '{nnn.v}' (length=7)
	 * 1 => string '' (length=0)
	 * 2 => string '' (length=0)
	 * 3 => string '' (length=0)
	 * 4 => string '' (length=0)
	 * 5 => string '' (length=0)
	 * 6 => string '' (length=0)
	 * 7 => string '' (length=0)
	 * 8 => string '' (length=0)
	 * 9 => string '' (length=0)
	 * 10 => string 'nnn.v' (length=5)
	 *
	 * array (size=4)
	 * 0 => string '{if:aa:bb:vvv}fffb{tttt:hh}bbb{if:4=5:7}888{/if}hh{/if}' (length=55)
	 * 1 => string 'if' (length=2)
	 * 2 => string 'aa:bb:vvv' (length=9)
	 * 3 => string 'fffb{tttt:hh}bbb{if:4=5:7}888{/if}hh' (length=36)
	 *
	 * @param string $subject
	 * @param array $param
	 * @return string
	 */
	public function init($subject, $param = array()) {
		$this->set($param);
		$matches = array();
		return preg_replace_callback('#
				{(if|for|switch):([^{}]*)}((?:[^{}]*|(?R))*){/\1}
				|{([\w_]+):([^{}]*)}
				|{([^{}?]+)\?([^{}?]*)}
				|{([^{}=:]+)=([^{}?=:]*)}
				|{([^{}]+)}
				#isx', array($this, '_callback'), $subject);
	}
	
	private function _callback($args) {
		switch (count($args)) {
			case 4:
				return $this->_systemFunction($args[2], $args[3], $args[1]);
			case 6:
				return $this->_function($args[4], $args[5]);
			case 8:
				return $this->_lambda($args[6], $args[7]);
			case 10:
				return $this->_assign($args[8], $args[9]);
			case 11:
			default:
				return $this->_getValue(array_pop($args));
		}
	}
	
	private function _systemFunction($condition, $content, $tag = 'if') {
		switch ($tag) {
			case 'if':
				return '';
			case 'for':
				return $this->_for($condition, $content);
			case 'switch':
				return $this->_switch($condition, $content);
			default:
				return null;
		}
	}
	
	private function _switch($condition, $content) {
		$args = explode(',', $condition);
		if (count($args) == 2) {
			return preg_replace_callback('/{case:([^{}]+)}/i', 'hhh', $content);
		}
		return null;
	}
	
	private function _for($arg, $content) {
		$args = explode(',', $arg);
		$values = $this->get($args[0], array());
		$keyTag = 'key';
		$valueTag = 'value';
		$end = count($values);
		$condition = null;
		switch (count($args)) {
			case 2:
				if (preg_match('/^([_\b][\w_]*)$/', $args[1])) {
					$valueTag = $args[1];
				} elseif (is_numeric($args[1])) {
					$end = $args[1];
				} else {
					$condition = $args[1];
				}
				break;
			case 3:
				$keyTag = $args[1];
				if (preg_match('/^([_\b][\w_]*)$/', $args[2])) {
					$valueTag = $args[2];
				} elseif (is_numeric($args[2])) {
					$end = $args[2];
				} else {
					$condition = $args[2];
				}
				break;
			case 4:
				$keyTag = $args[1];
				$values = $args[2];
				if (is_numeric($args[3])) {
					$end = $args[3];
				} else {
					$condition = $args[3];
				}
				break;
			case 5:
				$keyTag = $args[1];
				$values = $args[2];
				$end = $args[3];
				$condition = $args[4];
				break;
			default:
				break;
		}
		$result = '';
		foreach ($values as $key => $value) {
			if ($end < 1) {
				break;
			}
			$end--;
			if (!$this->_toCondition($condition, $keyTag)) {
				continue;
			}
			$this->set($keyTag, $key);
			$this->set($valueTag, $value);
			$result .= $this->init($content);
		}
		$this->delete($keyTag, $valueTag);
		return $result;
	}
	
	private function _function($name, $args) {
		$keys = explode(',', $args);
		foreach ($keys as &$value) {
			$value = $this->_toString($value);
		}
		if (function_exists($name)) {
			return call_user_func_array($name, $keys);
		}
		return call_user_func_array(array($this, $name), $keys);
	}
	
	private function _lambda($condition, $results) {
		$args = explode(':', $results, 2);
		if ($this->_toCondition($condition)) {
			return $this->_toString($args[0]);
		}
		if (count($args) >= 2) {
			return $this->_toString($args[1]);
		}
		return null;
	}
	
	private function _assign($keys, $values) {
		$this->set(ArrayExpand::combine(explode(',', $keys), explode(',', $values)));
	}
	
	private function _getValue($key) {
		$keys = explode(',', $key, 2);
		return $this->_toString($keys[0], count($keys) >= 2 ? $keys[1] : null);
	}
	
	private function _toCondition($arg, $firstKey = null) {
		$args = array();
		preg_match('/([^\<\>=\!]*)([\<\>=!]+)([^\<\>=\!]+)/', $arg, $args);
		$args[0] = $args[0] == '' ? $this->_toString($firstKey) : $this->_toString($args[0]);
		$args[2] = $this->_toString($args[2]);
		switch ($args[1]) {
			case '<':
				return $args[0] < $args[2];
			case '>':
				return $args[0] > $args[2];
			case '=':
			case '==':
			case '===':
				return $args[0] == $args[2];
			case '<=':
			case '=<':
				return $args[0] <= $args[2];
			case '>=':
			case '=>':
				return $args[0] >= $args[2];
			case '!=':
				return $args[0] != $args[2];
			default:
				return false;
		}
	}

	/**
	 * 获取值
	 * @param string $arg
	 * @param string $default
	 * @return array|bool|string
	 */
	private function _toString($arg, $default = null) {
		if (null == $arg || $arg == '') {
			return null;
		}
		if (substr($arg, 0, 1) == '\'') {
			return substr($arg, 1);
		}
		if (is_numeric($arg)) {
			return $arg;
		}
		if ('t' == $arg || 'T' == $arg) {
			return true;
		}
		if ('f' == $arg || 'F' == $arg) {
			return false;
		}
		if ($this->has($arg)) {
			return $this->get($arg);
		}
		return $this->_toString($default);
	}
}