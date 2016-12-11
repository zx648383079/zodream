<?php
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\Interfaces\FilterObject;

class EmailFilter extends FilterObject {
    protected $error = '不是有效的邮箱！';

    public function filter($arg) {
        return filter_var($arg, FILTER_VALIDATE_EMAIL);
    }
}