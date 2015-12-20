<?php
namespace Zodream\Infrastructure\Traits;

trait ValidataTrait {
	/**
	 * 验证数据
	 *
	 * @param array $request 要验证的数据
	 * @param array $param 验证的规则
	 * @return array
	 */
	protected function validata($request, $param) {
		$vali   = new Validate();
		$result = $vali->make($request, $param);
		if (!$result) {
			$result = $vali->error;
		}
		return $result;
	}
}