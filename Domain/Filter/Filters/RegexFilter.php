<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

class RegexFilter extends StringFilter {
    protected $_defaultOption = array(
        'regex' => '/.+/',
        'min' => 0,
        'max' => PHP_INT_MAX,
    );

    public function filter($arg) {
        $arg = parent::filter($arg);
        $matches = array();
        if (!preg_match($this->_option['regex'], $arg, $matches)) {
            return null;
        }
        return $matches[0];
    }

    public function setOption($option)
    {
        if (is_string($option)) {
            $option = array(
                'regex' => $option
            );
        }
        parent::setOption($option);
    }
}