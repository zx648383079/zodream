<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 16:08
 */
namespace Zodream\Domain\Filter;

class DataFilter {
    public static function filter() {

    }

    public static function validate() {

    }

    private static function _splitFilters($value, $arg) {
        $args = explode('|', $arg);
        foreach ($args as $item) {

        }
    }

    private static function _splitFilter($value, $arg) {
        list($filter, $option) = StringExpand::explode($value, ':', 2);
        switch (strtolower($filter)) {
            case 'required':
                return ;
        }
    }
}