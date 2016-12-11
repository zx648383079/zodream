<?php
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\Interfaces\FilterObject;

class RawFilter extends FilterObject {
    public function filter($arg) {
        return filter_var($arg, FILTER_UNSAFE_RAW);
    }
}