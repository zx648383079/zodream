<?php
namespace Zodream\Infrastructure\Interfaces;
/**
 * Class FilterObject
 * @package Zodream\Infrastructure\Interfaces
 */
abstract class FilterObject {
    protected $_defaultOption = array();
    protected $_option = array();

    protected $error = '验证失败！';

    public function __construct($option = null) {
        $this->setOption($option);
    }

    /**
     * SET OPTION
     * @param string|array|object $option
     */
    public function setOption($option) {
        $this->_option = (array)$option + $this->_defaultOption;
    }

    /**
     * GET OPTIONS
     * @return array
     */
    public function getOption() {
        return $this->_option;
    }

    /**
     * FILTER VALUE
     * @param $arg
     * @return mixed
     */
    public function filter($arg) {
        return $arg;
    }

    /**
     * VALIDATE VALUE,IF IS NULL OR EQUAL WHAT AFTER FILTER VALUE,RETURN TRUE, OR FALSE.
     * @param $arg
     * @return bool
     */
    public function validate($arg) {
        if (is_null($arg) || $arg == '') {
            return true;
        }
        $filtered = $this->filter($arg);
        return $filtered == $arg;
    }

    /**
     * GET ERROR IF VALIDATE FALSE
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    public function setError($error) {
        if (empty($error)) {
            return;
        }
        $this->error = $error;
    }
}