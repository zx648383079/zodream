<?php
namespace Zodream\Infrastructure\Log;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/9/8
 * Time: 14:23
 */
class Logger {
    protected $logs;

    public function __construct() {
        $this->clear();
    }

    public function getLogs($level = false) {
        return false === $level ? $this->logs : $this->logs[$level];
    }

    public function clear() {
        $this->logs = array(
            'emergency' => array(),
            'alert' => array(),
            'critical' => array(),
            'error' => array(),
            'warning' => array(),
            'notice' => array(),
            'info' => array(),
            'debug' => array(),
        );
    }

    public function log($level, $message, array $context = array()) {
        $this->logs[$level][] = $message;
    }

    public function emergency($message, array $context = array()) {
        return $this->log(__FUNCTION__, $message, $context);
    }

    public function alert($message, array $context = array()) {
        return $this->log(__FUNCTION__, $message, $context);
    }

    public function critical($message, array $context = array()) {
        return $this->log(__FUNCTION__, $message, $context);
    }

    public function error($message, array $context = array()) {
        return $this->log(__FUNCTION__, $message, $context);
    }

    public function warning($message, array $context = array()) {
        return $this->log(__FUNCTION__, $message, $context);
    }

    public function notice($message, array $context = array()) {
        return $this->log(__FUNCTION__, $message, $context);
    }

    public function info($message, array $context = array()) {
        return $this->log(__FUNCTION__, $message, $context);
    }

    public function debug($message, array $context = array()) {
        return $this->log(__FUNCTION__, $message, $context);
    }
}