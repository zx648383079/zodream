<?php
namespace Zodream\Infrastructure\Security;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/9
 * Time: 14:12
 */
class AuthCode {
    protected $key;

    public function setKey($key) {
        $this->key = $key;
        return $this;
    }

    public function getKey() {
        return $this->key;
    }

    public function encrypt($data) {

    }

    public function decrypt($data) {

    }
}