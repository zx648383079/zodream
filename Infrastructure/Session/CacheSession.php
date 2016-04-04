<?php
namespace Zodream\Infrastructure\Session;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/6
 * Time: 9:56
 */
class CacheSession extends Session {

    /**
     * @var \Zodream\Infrastructure\Caching\Cache
     */
    private $_cache;

    public function useCustomStorage() {
        return true;
    }

    public function readSession($id) {
        $data = $this->_cache->get($this->calculateKey($id));
        return $data === false ? '' : $data;
    }


    public function writeSession($id, $data) {
        $this->_cache->set($this->calculateKey($id), $data, $this->getTimeout());
    }

    public function destroySession($id) {
        return $this->_cache->delete($this->calculateKey($id));
    }

    public function getTimeout() {
        return time();
    }

    protected function calculateKey($id) {
        return [__CLASS__, $id];
    }
}