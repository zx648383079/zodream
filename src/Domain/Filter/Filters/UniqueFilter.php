<?php
namespace Zodream\Domain\Filter\Filters;

use Zodream\Domain\Filter\FilterModel;
use Zodream\Infrastructure\Interfaces\FilterObject;

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