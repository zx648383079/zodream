<?php
namespace Zodream\Domain\Model;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/12
 * Time: 16:13
 */
use Zodream\Domain\Access\Auth;
use Zodream\Infrastructure\Interfaces\UserObject;

abstract class UserModel extends Model implements UserObject {

    public static function getRememberTokenName() {
        return 'remember_token';
    }

    public static function getIdentityName() {
        return 'id';
    }
    
    public function getIdentity() {
        return $this->get(static::getIdentityName());
    }

    /**
     * @return string
     */
    public function getRememberToken() {
        return $this->get(static::getRememberTokenName());
    }

    /**
     * @param string $token
     * @return static
     */
    public function setRememberToken($token) {
        $this->set(static::getRememberTokenName(), $token);
        $this->save();
        return $this;
    }

    public function login() {
        $this->invoke(static::BEFORE_LOGIN, [$this]);
        Auth::login($this);
        $this->invoke(static::AFTER_LOGIN, [$this]);
        return true;
    }
    
    public function logout() {
        $this->invoke(static::BEFORE_LOGOUT, [$this]);
        Auth::logout();
        $this->invoke(static::AFTER_LOGOUT, [$this]);
        return true;
    }

    /**
     * 根据 记住密码 token 获取用户
     * @param integer $id
     * @param string $token
     * @return UserObject
     */
    public static function findByRememberToken($id, $token) {
        return static::find([
            static::getRememberTokenName() => $token,
            static::getIdentityName() => $id
        ]);
    }

    public static function signInAccount($username, $password) {
        throw new \Exception('undefined method');
    }

    public static function findByIdentity($id) {
        return static::find($id);
    }

    public static function findByToken($token) {
        throw new \Exception('undefined method');
    }

}