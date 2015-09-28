<?php 
namespace App\Lib\Role;
/**
* 二进制法
*/

class RBinary implements IBase
{
	public static function judge($role , $roles)
	{
		$role = intval($role);
		$roles = intval($roles);
		return $roles&$role;
	}
	
	public static function compose()
	{
		$arr = func_get_args();
		if(is_array($arr[0]))
		{
			$arr = $arr[0];
		}
		
		$roles = 0;
		
		foreach ($arr as $value) {
			$roles += intval($value);
		}
		
		return $roles;
	}
}