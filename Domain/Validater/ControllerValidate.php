<?php
namespace Zodream\Domain\Validater;

use Zodream\Infrastructure\DomainObject\ValidaterObject;
class ControllerValidate implements ValidaterObject {
	/**
	 * 控制器方法前提验证
	 * @param string $role
	 * @return boolean
	 */
	public static function make($role) {
		
		return true;
	}
}