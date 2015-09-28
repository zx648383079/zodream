<?php 
namespace App\Lib\Role;
/**
* 符号分割法
*/

class RComma implements IBase
{
	public static function judge($role , $roles)
	{
		$roles = explode(',', $roles );
		return in_array($role , $roles);
	}
	
	public static function compose()
	{
		$arr = func_get_args();
		if(is_array($arr[0]))
		{
			$arr = $arr[0];
		}
		
		$roles = implode(',',$arr);
		
		return $roles;
	}
}