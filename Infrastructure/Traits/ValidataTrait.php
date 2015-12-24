<?php
namespace Zodream\Infrastructure\Traits;

use Zodream\Domain\Validate;

trait ValidateTrait {
	/**
	 * 验证数据
	 *
	 * @param array $request 要验证的数据
	 * @param array $param 验证的规则
	 * @return array
	 */
	protected function validate($request, $param) {
		$validate   = new Validate();
		$result = $validate->make($request, $param);
		if (!$result) {
			$result = $validate->getError();
		}
		return $result;
	}
}