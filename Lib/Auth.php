<?php
namespace App\Lib;

/******************************************************
*用户类
*
*********************************************************/
use App;
use App\Model\UserModel;

class Auth
{
	
	public static $userModel;
	/*
	* 判断是否登录
	*
	* @access public static
	*
	* @return 返回True|False,
	*/
	public static function user()
	{
		$id = App::session('user');
		if( !empty($id ) )
		{
			if(empty(self::$userModel))
			{
				$user = new UserModel();
				$user -> assignRow('id',$id);
				self::$userModel = $user;
			}
			return self::$userModel;
		}else{
			return false;
		}
	}

	/**
	* 判断是否是游客
	*
	* @return bool
	*/
	public static function guest()
	{
		$id = self::getId();
		return empty($id);
	}

	/**
	 * @return bool|string
     */
	private static function getId()
	{
		$id = App::session('user');
		$token = App::cookie('token');
		if(!empty($id))
		{
			return $id;
		}else if(!empty($token))
		{
			$user = new UserModel();
			$id = $user -> findByToken($token);
			App::session('user' , $id);
			$user -> assignRow('id',$id);
			self::$userModel = $user;
			return $id;
		}
		return null;
	}
}