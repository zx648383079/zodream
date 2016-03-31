<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;
class RequiredFilter extends FilterObject {
    public function validate($arg) {
        return $arg !== null && trim($arg) !== '';
    }
}