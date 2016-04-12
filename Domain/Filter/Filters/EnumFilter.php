<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;

class EnumFilter extends FilterObject {
    protected $error = '不在枚举中！';

    public function validate($arg) {
        return in_array($arg, $this->_option);
    }

    public function setOption($option) {
        parent::setOption(explode(',', $option));
    }
}