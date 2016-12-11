<?php
namespace Zodream\Domain\Model;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/12
 * Time: 16:13
 */
use Zodream\Infrastructure\Interfaces\UserObject;
use Zodream\Service\Factory;

abstract class UserModel extends Model implements UserObject {
    
    public function getId() {
        return $this->get($this->primaryKey[0]);
    }

    public function login($user) {
        $this->runBehavior(static::BEFORE_LOGIN);
        Factory::session()->set('user', $user);
        $this->runBehavior(static::AFTER_LOGIN);
        return true;
    }
    
    public function logout() {
        $this->runBehavior(static::BEFORE_LOGOUT);
        Factory::session()->destroy();
        $this->runBehavior(static::AFTER_LOGOUT);
        unset($this);
        return true;
    }

}