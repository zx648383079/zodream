<?php
namespace Zodream\Infrastructure\Traits;

use Zodream\Domain\Response\Ajax;
trait AjaxTrait {
	/**
	 * 返回JSON数据
	 *
	 * @param array|string $data 要传的值
	 * @param string $type 返回类型
	 */
	protected function ajaxReturn($data, $type = 'JSON') {
		Ajax::ajaxReturn($data, $type);
	}
}