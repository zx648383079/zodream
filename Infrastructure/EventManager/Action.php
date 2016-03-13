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
        if (!class_exists($this->class) || !function_exists($this->function)) {
            require $this->file;
        }
        if (empty($this->class)) {
            $this->_runWithFunction($args);
            return;
        }
        if (empty($this->function)) {
            $this->_runWithClass($args);
            return;
        }
        if (!class_exists($this->class)) {
            return;
        }
        $class = $this->class;
        $instance = new $class;
        call_user_func_array(array($instance, $this->function), (array)$args);
    }

    private function _runWithClass($args) {
        if (!class_exists($this->class)) {
            return;
        }
        $class = $this->class;
        new $class($args);
    }

    private function _runWithFunction($args) {
        if (empty($this->function)) {
            return;
        }
        if ($this->function instanceof \Closure) {
            $function = $this->function;
            $function($args);
            return ;
        }
        if (!class_exists($this->class)) {
            return;
        }
        call_user_func_array($this->function, (array)$args);
    }
}