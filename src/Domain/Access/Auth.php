<?php
namespace Zodream\Domain\Access;
/**
 * AUTH CONTROL
 *
 * @author Jason
 */
use Zodream\Infrastructure\Interfaces\AuthObject;
use Zodream\Infrastructure\Interfaces\UserObject;
use Zodream\Service\Factory;

class Auth implements AuthObject {

	/**
	 * @var bool|UserObject
	 */
	protected static $identity = false;

	/**
	 * @param bool $refresh
	 * @return bool|UserObject
	 */
	public static function getIdentity($refresh = false) {
		if (static::$identity === false || $refresh) {
			static::$identity = Factory::session()->get('user');
		}
		return static::$identity;
	}

    /**
     * 用户id
     * @return int
     */
	public static function id() {
	    if (empty(static::user())) {
	        return false;
        }
        return static::user()->getId();
    }

	/**
	 * 获取登录
	 * @return bool|UserObject
	 */
	public static function user() {
		if (!empty(static::getIdentity())) {
			return static::$identity;
		}
		return false;
	}

	/**
	 * 判断是否是游客
	 * @return bool
	 */
	public static function guest() {
		return empty(static::getIdentity());
	}
}