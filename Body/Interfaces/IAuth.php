<?php
namespace App\Body\Interfaces;

interface IAuth {
	/**
	 * 获取用户信息
	 */
	static function user();
	
	/**
	 * 判断是否游客
	 */
	static function guest();
}