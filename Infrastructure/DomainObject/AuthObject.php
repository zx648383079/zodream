<?php
namespace Zodream\Infrastructure\DomainObject;

interface AuthObject {
	/**
	 * 获取用户信息
	 */
	static function user();
	
	/**
	 * 判断是否游客
	 */
	static function guest();
}