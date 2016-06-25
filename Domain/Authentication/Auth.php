<?php
namespace Zodream\Domain\Authentication;
/**
 * 二进制法
 *
 * @author Jason
 */
use Zodream\Infrastructure\DomainObject\AuthObject;
use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\Traits\SingletonPattern;

class Auth extends MagicObject implements AuthObject {
	use SingletonPattern;
	protected function __construct() {
		if (Factory::session()->has('user')) {
			$this->set(Factory::session()->get('user'));
			Roles::setRoles($this->get('roles', array()));
		}
	}

	/**
	 * 保存
	 */
	public function save() {
		return Factory::session()->set('user', $this->_data);
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