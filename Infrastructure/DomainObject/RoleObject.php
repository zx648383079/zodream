<?php
namespace Zodream\Infrastructure\DomainObject;

interface RoleObject {
	/**
	 * 判断权限
	 *
	 * @param string $role 需要的权限
	 * @param string $roles 拥有的权限
	 */
	static function judge($role, $roles);
	
	/**
	 * 合成
	 *
	*/
	static function compose();
}