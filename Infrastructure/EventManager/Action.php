<?php
namespace Zodream\Infrastructure\EventManager;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/10
 * Time: 9:49
 */
class Action {
    protected $class;
    protected $function;
    protected $file;

    public function __construct($class, $function = null, $file = null) {
        $this->class = $class;
        $this->function = $function;
        if (!empty($file) && !is_file($file)) {
            $file = APP_DIR.'/'.ltrim($file, '/');
        }
        $this->file = $file;
    }

    public function run($args = array()) {
        if ($this->class instanceof \Closure) {
            return call_user_func_array($this->class, (array)$args);
        }
        if (strpos($this->class, '::') === false &&
            (!class_exists($this->class) || !function_exists($this->function))) {
            return require($this->file);
        }
        if (empty($this->class)) {
            return $this->_runWithFunction($args);
        }
        if (empty($this->function)) {
            return $this->_runWithClass($args);
        }
        if (!class_exists($this->class)) {
            return false;
        }
        $class = $this->class;
        $instance = new $class;
        return call_user_func_array(array($instance, $this->function), (array)$args);
    }

    private function _runWithClass($args) {
        if (strpos($this->class, '::') !== false) {
            return call_user_func_array($this->class, $args);
        }
        if (!class_exists($this->class)) {
            return false;
        }
        $class = $this->class;
        return new $class($args);
    }

    private function _runWithFunction($args) {
        if (empty($this->function)) {
            return false;
        }
        if ($this->function instanceof \Closure) {
            $function = $this->function;
            return $function($args);
        }
        if (!class_exists($this->class)) {
            return false;
        }
        return call_user_func_array($this->function, (array)$args);
    }
}