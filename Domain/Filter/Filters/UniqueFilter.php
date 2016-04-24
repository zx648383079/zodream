<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Domain\Filter\FilterModel;
use Zodream\Infrastructure\DomainObject\FilterObject;

class UniqueFilter extends FilterObject {
    protected $error = '不是唯一！';

    public function validate($arg) {
        return intval(FilterModel::getCount(
            $this->_option['table'], 
            $this->_option['column'], $arg)) < 1;
    }

    public function setOption($option) {
        list($table, $column) = explode('.', $option, 2);
        parent::setOption(array(
            'table' => $table,
            'column' => $column
        ));
    }
}