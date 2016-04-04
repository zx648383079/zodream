<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;

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