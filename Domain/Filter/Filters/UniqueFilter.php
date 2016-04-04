<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;

class UniqueFilter extends FilterObject {
    protected $error = '不是唯一！';

    public function validate($arg) {
        return true;
    }

    public function setOption($option) {
        explode('.', $option);
        parent::setOption((array)$option);
    }
}