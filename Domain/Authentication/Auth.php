<?php
namespace Zodream\Domain\Authentication;
/**
 * 二进制法
 *
 * @author Jason
 */
use Zodream\Infrastructure\DomainObject\AuthObject;
use Zodream\Infrastructure\Session;

class Auth implements AuthObject {
	public static function user() {
		if (Session::getInstance()->has('user')) {
			return Session::getInstance()->get('user');
		}
		return false;
	}
	
	public static function guest() {
		return !Session::getInstance()->has('user');
	}
}