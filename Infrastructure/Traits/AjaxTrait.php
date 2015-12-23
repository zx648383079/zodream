<?php
namespace Zodream\Infrastructure\Traits;

use Zodream\Infrastructure\Response\Ajax;
trait AjaxTrait {
	/**
	 * 返回JSON数据
	 *
	 * @param array|string $data 要传的值
	 * @param string $type 返回类型
	 */
	protected function ajaxJson($data, $type = 'JSON') {
		Ajax::view($data, $type);
	}
}