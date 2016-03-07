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
	public function validate($request, $args) {
		$result = DataFilter::validate($request, $args);
		return !in_array(false, $result);
	}
	
}