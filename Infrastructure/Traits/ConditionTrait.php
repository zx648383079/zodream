<?php
namespace Zodream\Infrastructure\Traits;
/**
 * 自定义公式
 * @author zx648
 *
 */
trait ConditionTrait {
	
	private $_switchCondition = null;
	private $_switchValue;
	
	/**
	 * 拓展switch
	 * @param string $condition 条件或要输出的值
	 * @param string $value 要输出的值
	 */
	public function swi($condition, $value = null) {
		if (null == $value) {
			$this->_switchCondition = null;
			$this->_switchValue = $condition;
		} else  {
			$this->_switchCondition = $condition;
			$this->_switchValue = $value;
		}
	}
	
	/**
	 * 拓展case
	 * @param string|boolean $condition 条件
	 * @param string $value 可以更改输出的值，不必先用 $this->swi
	 */
	public function cas($condition, $value = null) {
		if (null !== $value) {
			$this->_switchValue = $value;
		}
		if ((null === $this->_switchCondition && true === $condition) ||
			($this->_switchCondition !== null && $this->_switchCondition == $condition)) {
			echo $this->_switchValue;
		}
	}

	/**
	 * 替换标志
	 * @param string $name
	 * @param string|integer|array $key
	 */
	public function tag($name, $key) {
		$args = array();
		if (!is_array($key)) {
			for ($i = 1; $i < func_num_args(); $i+=2) {
				$args[func_get_arg($i)] = func_get_arg($i + 1);
			};
		} else {
			$args = $key;
		}
		if (isset($args[$name])) {
			echo $args[$name];
		}
	}
	
	
}