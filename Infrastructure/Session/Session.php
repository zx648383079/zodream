<?php
namespace Zodream\Infrastructure\Session;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/6
 * Time: 9:56
 */
class Session implements \ArrayAccess {

    private $_cookieParams = array(
        'httponly' => true
    );

    public function useCustomStorage() {
        return false;
    }

    public function isActive() {
        return session_status() == PHP_SESSION_ACTIVE;
    }

    public function open() {
        if ($this->isActive()) {
            return;
        }
        register_shutdown_function(array($this, 'close'));
        $this->_setCookieParamsInternal();
        $this->useCookie(true);
        $this->useTransparentSessionID(false);
        $this->savePath(APP_DIR.'/tmp');
        @session_start();
    }

    protected function registerSessionHandler() {
        if ($this->useCustomStorage()) {
            @session_set_save_handler(
                [$this, 'openSession'],
                [$this, 'closeSession'],
                [$this, 'readSession'],
                [$this, 'writeSession'],
                [$this, 'destroySession'],
                [$this, 'gcSession']
            );
        }
    }

    public function openSession($savePath, $sessionName) {
        return true;
    }

    public function closeSession() {
        return true;
    }

    public function readSession($id) {
        return '';
    }

    public function writeSession($id, $data) {
        return true;
    }

    public function destroySession($id) {
        return true;
    }

    public function gcSession($maxLifetime) {
        return true;
    }

    public function close() {
        if ($this->isActive()) {
            @session_write_close();
        }
    }

    /**
     * gc自动执行的时间
     * @param $value
     */
    public function gcProbability($value) {
        if ($value >= 0 && $value <= 100) {
            ini_set('session.gc_probability', floor($value * 21474836.47));
            ini_set('session.gc_divisor', 2147483647);
        }
    }

    /**
     * gc执行时间限制
     * @param $value
     */
    public function timeout($value) {
        ini_set('session.gc_maxlifetime', $value);
    }

    public function getCookieParams() {
        return array_merge(session_get_cookie_params(), array_change_key_case($this->_cookieParams));
    }

    public function setCookieParams(array $value) {
        $this->_cookieParams = $value;
    }

    private function _setCookieParamsInternal()
    {
        $data = $this->getCookieParams();
        extract($data);
        if (isset($lifetime, $path, $domain, $secure, $httponly)) {
            session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        }
    }

    public function id($value = null) {
        return session_id($value);
    }

    public function savePath($path = null) {
        if (null == $path) {
            return session_save_path();
        }
        if (is_dir($path)) {
            return session_save_path($path);
        }
        return false;
    }

    public function useCookie($value) {
        if ($value === false) {
            ini_set('session.use_cookies', '0');
            ini_set('session.use_only_cookies', '0');
        } elseif ($value === true) {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
        } else {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '0');
        }
    }

    public function useTransparentSessionID($value) {
        ini_set('session.use_trans_sid', $value ? '1' : '0');
    }

    public function count() {
        $this->open();
        return count($_SESSION);
    }

    public function get($key, $defaultValue = null) {
        $this->open();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
    }

    public function set($key, $value) {
        $this->open();
        $_SESSION[$key] = $value;
    }

    public function delete($key = null) {
        $this->open();
        if (null == $key) {
            foreach (array_keys($_SESSION) as $key) {
                unset($_SESSION[$key]);
            }
            return true;
        }
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        }
        return null;
    }

    public function destroy() {
        if ($this->isActive()) {
            @session_unset();
            @session_destroy();
        }
    }

    public function has($key) {
        $this->open();
        return isset($_SESSION[$key]);
    }

    public function name($value = null) {
        return session_name($value);
    }

    public function offsetExists($offset) {
        return $this->has($offset);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset) {
        return $this->delete($offset);
    }

}