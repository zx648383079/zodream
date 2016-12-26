<?php
namespace Zodream\Domain\Html;

use Zodream\Service\Factory;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Http\Request;

class VerifyCsrfToken {
	/**
	 * 生成Csrf
	 * @return string
     */
	public static function create() {
		$csrf = StringExpand::random(10);
		Factory::session()->set('_csrf', $csrf);
		Factory::view()->set('_csrf', $csrf);
		return $csrf;
	}

	/*
	 * 验证
	 * @return bool
	 */
	public static function verify() {
		if (self::get() === Request::request('_csrf')) {
			return true;
		}
		throw new \Exception('Csrf验证失败！');
	}

	/**
	 * 获取已经生成的Csrf
	 * @return string
	 */
	public static function get() {
		return Factory::session()->get('_csrf');
	}
}