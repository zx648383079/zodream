<?php
namespace App\Lib\Role;

use App;
use App\Lib\Account;

class RVerify {
	public static function make($role) {
		if (is_object($role) && !$role()) {
			App::redirect('/');
			return false;
		} else if (is_string($role) && !empty($role)) {
			$roles = explode(',', $role);
			foreach ($roles as $value) {
				if (self::_verify($value) === false) {
					return false;
				}
			}
		}
		return true;
	}
	
	private static function _verify($role) {
		switch ($role) {
			case '?':
				if (!Account::guest()) {
					App::redirect('/');
					return false;
				}
				break;
			case '1':
				if (Account::guest()) {
					App::redirect('account');
					return false;
				}
				break;
			case 'p':
				if (!App::$request->isPost()) {
					App::redirect('/', 4, '您不能直接访问此页面！', '400');
					return false;
				}
				break;
			case '!':
				App::redirect('/', 4, '您访问的页面暂未开放！', '413');
				return false;
				break;
			default:
				if (!self::judge($role)) {
					App::redirect('account', 4, '您无权操作！', '401');
					return false;
				}
				break;
		}
		return true;
	}
	
	/**
	 * 判断权限是否符合
	 * @param string $role 权限
	 */
	public static function judge($role) {
		if (Account::guest()) {
			return empty($role);
		} else {
			return RComma::judge($role, Account::user()->role()->roles);
		}
	}
}
