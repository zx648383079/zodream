<?php
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\Interfaces\FilterObject;
class RequiredFilter extends FilterObject {
    protected $error = '必填验证失败！';

    public function validate($arg) {
        if (is_null($arg)) {
            return false;
        }
        if (is_string($arg)) {
            return trim($arg) !== '';
        }
        if (!is_array($arg)) {
            return true;
        }
        foreach ($arg as $item) {
            if ($this->validate($item)) {
                return true;
            }
        }
        return false;
    }
}