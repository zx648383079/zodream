<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;

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