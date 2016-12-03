<?php
namespace Zodream\Infrastructure\Session;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/6
 * Time: 9:56
 */
use Zodream\Service\Factory;
class CacheSession extends Session {

    public function useCustomStorage() {
        return true;
    }

    public function readSession($id) {
        $data = Factory::cache()->get($this->calculateKey($id));
        return $data === false ? '' : $data;
    }


    public function writeSession($id, $data) {
        Factory::cache()->set($this->calculateKey($id), $data, $this->getTimeout());
    }

    public function destroySession($id) {
        return  Factory::cache()->delete($this->calculateKey($id));
    }

    public function getTimeout() {
        return time();
    }

    protected function calculateKey($id) {
        return [__CLASS__, $id];
    }
}