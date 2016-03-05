<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;

class NoneFilter extends FilterObject {
    public function filter($arg) {
        return $arg;
    }

    public function validate($arg) {
        return true;
    }
}