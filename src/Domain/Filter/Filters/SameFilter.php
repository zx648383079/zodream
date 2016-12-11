<?php
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\Interfaces\FilterObject;

class SameFilter extends FilterObject {
    protected $error = '不相等！';

    public function validate($arg) {
        return $arg == $this->_option[0];
    }

    public function setOption($option)
    {
        parent::setOption((array)$option);
    }
}