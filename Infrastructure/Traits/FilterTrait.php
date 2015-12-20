<?php
namespace Zodream\Infrastructure\Traits;

trait FilterTrait {
	/**
	 * 在执行之前做规则验证
	 * @param string $func 方法名
	 * @return boolean
	 */
	public function beforeFilter($func) {
		if (isset($this->rules)) {
			$func = str_replace(APP_ACTION, '', $func);
			$role = isset($this->rules['*']) ? $this->rules['*'] : '';
			$role = isset($this->rules[$func]) ? $this->rules[$func] : $role;
			return RVerify::make($role);
		}
		if (method_exists($this, '_initialize')) {
			$this->_initialize();
		}
		return TRUE;
	}
	
	/**
	 * 执行完了
	 */
	public function afterFilter() {
		
	}
}