<?php
namespace Zodream\Domain\Filter\Filters;

class PhoneFilter extends RegexFilter {
    protected $error = '手机号码错误！';

    public function filter($arg) {
        $arg = parent::filter($arg);
        $matches = array();
        if (!preg_match($this->_option['regex'], $arg, $matches)) {
            return null;
        }
        return $matches[0];
    }

    public function setOption($option) {
        parent::setOption('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#');
    }
}