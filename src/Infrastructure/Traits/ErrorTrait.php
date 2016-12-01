<?php
namespace Zodream\Infrastructure\Traits;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/11/9
 * Time: 11:43
 */
trait ErrorTrait {
    private $_errors = [];

    private $_errorNo = 0;

    /**
     * SET ERROR AND ERRORNO
     * @param $error
     * @param $errorNo
     * @return $this
     */
    public function setError($error, $errorNo) {
        $this->_errors[] = $error;
        $this->_errorNo = $errorNo;
        return $this;
    }

    /**
     * GET ERROR
     * @return array
     */
    public function getError() {
        return $this->_errors;
    }

    /**
     * HAS ERROR
     * @return bool
     */
    public function hasError() {
        return !empty($this->_errors);
    }

    /**
     * GET LAST ERROR
     * @return array
     */
    public function getLastError() {
        return array_slice($this->_errors, -1);
    }
}