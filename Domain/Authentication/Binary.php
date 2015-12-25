<?php 
namespace Zodream\Domain\Authentication;
/**
 * 二进制法
 *
 * @author Jason
 */
use Zodream\Infrastructure\DomainObject\RoleObject;

class Binary implements RoleObject {
	public static function judge($role, $roles) {
		$role  = intval($role);
		$roles = intval($roles);
		return $roles&$role;
	}
	
	public static function compose() {
		$arr = func_get_args();
		if (is_array($arr[0])) {
			$arr = $arr[0];
		}
		$roles = 0;
		foreach ($arr as $value) {
			$roles += intval($value);
		}
		
		return $roles;
	}
}