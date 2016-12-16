<?php
namespace Zodream\Infrastructure\Traits;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/11/9
 * Time: 11:43
 */
trait ErrorTrait {
    protected $errors = [];

    /**
     * HAS ERROR
     * @return bool
     */
    public function hasError() {
        return !empty($this->errors);
    }

    public function getError($key = null) {
        if (empty($key)) {
            return $this->errors;
        }
        if (!array_key_exists($key, $this->errors)) {
            return array();
        }
        return $this->errors[$key];
    }

    public function getFirstError($key) {
        if (!array_key_exists($key, $this->errors)) {
            return null;
        }
        return current($this->errors[$key]);
    }

    /**
     * SET ERROR
     * @param $key
     * @param null $error
     * @return static
     */
    public function setError($key, $error = null) {
        if (is_array($key) && is_null($error)) {
            $this->errors = array_merge($this->errors, $key);
            return $this;
        }
        if (!array_key_exists($key, $this->errors)) {
            $this->errors[$key] = array();
        }
        $this->errors[$key][] = $error;
        return $this;
    }

    /**
     * GET LAST ERROR
     * @return array
     */
    public function getLastError() {
        return array_slice($this->errors, -1);
    }
}