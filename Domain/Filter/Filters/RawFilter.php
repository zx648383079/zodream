<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;

class RawFilter extends FilterObject {
    public function filter($arg) {
        return filter_var($arg, FILTER_UNSAFE_RAW);
    }
}