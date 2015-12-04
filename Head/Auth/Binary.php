<?php 
namespace Zodream\Head\Auth;
/*
 * 二进制法
 *
 * @author Jason
 * @time 2015-12-2
 */
use Zodream\Body\Interfaces\IRole;

class Binary implements IRole {
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