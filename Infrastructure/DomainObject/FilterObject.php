<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 12:34
 */
namespace Zodream\Infrastructure\DomainObject;

abstract class FilterObject {
    protected $_error         = 'ERROR！';
    protected $_defaultOption = array();
    protected $_option        = array();

    public function __construct($option = null) {
        $this->setOption($option);
    }

    public function setOption($option) {
        $this->_option = (array)$option + $this->_defaultOption;
    }

    public function getOption() {
        return $this->_option;
    }

    public function filter($arg) {
        return $arg;
    }

    public function validate($arg) {
        $filtered = $this->filter($arg);
        return !is_null($filtered) && $filtered == $arg;
    }

    public function setError($error) {
        $this->_error = $error;
    }

    public function getError() {
        return $this->_error;
    }
}