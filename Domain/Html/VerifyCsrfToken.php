<?php
namespace Zodream\Domain\Html;

use Zodream\Infrastructure\Error;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use  Zodream\Infrastructure\Session;
use Zodream\Infrastructure\Request;

class VerifyCsrfToken {
	/**
	 * 生成Csrf
	 * @return string
     */
	public static function create() {
		$csrf = StringExpand::random(10);
		Session::getInstance()->set('csrf', $csrf);
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
		return Session::getInstance()->get('csrf');
	}
}