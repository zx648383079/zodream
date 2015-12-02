<?php 
namespace App\Head\Auth;
/*
 * 符号分割法
 *
 * @author Jason
 * @time 2015-12-2
 */
use App\Body\Interfaces\IRole;

class Comma implements IRole {
	public static function judge($role, $roles) {
		$roles = explode(',', $roles);
		return in_array($role , $roles);
	}
	
	public static function compose() {
		$arr = func_get_args();
		if (is_array($arr[0])) {
			$arr = $arr[0];
		}
		$roles = implode(',', $arr);
		return $roles;
	}
}