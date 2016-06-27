<?php
namespace Zodream\Domain\Html;

use Zodream\Infrastructure\Error\Error;
use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Request;

class VerifyCsrfToken {
	/**
	 * 生成Csrf
	 * @return string
     */
	public static function create() {
		$csrf = StringExpand::random(10);
		Factory::session()->set('csrf', $csrf);
		return $csrf;
	}

	/*
	 * 验证
	 */
	public static function verify() {
		if (self::get() === Request::request('csrf')) {
			return;
		}
		Error::out('Csrf验证失败！', __FILE__, __LINE__);
	}

	/**
	 * 获取已经生成的Csrf
	 * @return string
	 */
	public static function get() {
		return Factory::session()->get('csrf');
	}
}