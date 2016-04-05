<?php
namespace Zodream\Domain\Authentication;
/**
 * 二进制法
 *
 * @author Jason
 */
use Zodream\Infrastructure\DomainObject\AuthObject;
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\Session;
use Zodream\Infrastructure\Traits\SingletonPattern;

class Auth extends MagicObject implements AuthObject {
	use SingletonPattern;
	protected function __construct() {
		if (Session::getInstance()->has('user')) {
			$this->set(Session::getInstance()->get('user'));
		}
	}

	/**
	 * 获取登录
	 * @return bool|static
	 */
	public static function user() {
		if (static::getInstance()->has()) {
			return static::getInstance();
		}
		return false;
	}

	/**
	 * 判断是否是游客
	 * @return bool
	 */
	public static function guest() {
		return !static::getInstance()->has();
	}
}