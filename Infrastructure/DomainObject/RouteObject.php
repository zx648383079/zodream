<?php
namespace Zodream\Infrastructure\DomainObject;

interface RouteObject {
	/**
	 * 获取路由
	 */
	static function get();
	
	/**
	 * 生成url
	 * @param string $file
	 */
	static function to($file);
}