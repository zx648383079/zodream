<?php
namespace Zodream\Domain\Filter\Filters;

class PhoneFilter extends RegexFilter {
    protected $error = '手机号码错误！';

    public function setOption($option) {
        parent::setOption('#^13[\d]{9}$|^14[57]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0678]{1}\d{8}$|^18[\d]{9}$#');
    }
}