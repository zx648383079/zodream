<?php
namespace Zodream\Domain;

use Zodream\Domain\Filter\DataFilter;
use Zodream\Infrastructure\Traits\ViewTrait;
abstract class Form {
	
	use ViewTrait;
	/**
	 * 验证POST数据
	 * @param string $args
	 * @return NULL[]
	 */
	public function validata($request, $args) {
		$result = DataFilter::validate($request, $args);
		return !in_array(false, $result);
	}
	
	/**
	 * 填充表单--编辑的时候用
	 */
	public function get() {
		
	}
	
	/**
	 * 从表单获取
	 */
	public function set() {
		
	}
	
}