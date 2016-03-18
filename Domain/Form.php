<?php
namespace Zodream\Domain;

use Zodream\Domain\Filter\DataFilter;
use Zodream\Infrastructure\Request;
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

	/**
	 * 执行方法
	 * @param string $action
	 * @return bool|mixed
	 */
	public function runAction($action) {
		if (!Request::getInstance()->isPost()) {
			return false;
		}
		if (!method_exists($this, $action)) {
			return false;
		}
		$args = func_get_args();
		unset($action[0]);
		return call_user_func_array(array($this, $action), $args);
	}
}