<?php
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;
class RequiredFilter extends FilterObject {
    protected $error = '必填验证失败！';

    public function validate($arg) {
        return $arg !== null && trim($arg) !== '';
    }
}