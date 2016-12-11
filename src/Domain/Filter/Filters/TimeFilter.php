<?php
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\Interfaces\FilterObject;

class TimeFilter extends FilterObject {
    protected $error = '不是时间类型！';

    public function filter($arg) {
        $arg = strtotime($arg);
        if ($arg === false) {
            return null;
        }
        return $arg;
    }

    public function validate($arg) {
        return strtotime($arg) !== false;
    }
}