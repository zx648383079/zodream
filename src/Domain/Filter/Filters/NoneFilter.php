<?php
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