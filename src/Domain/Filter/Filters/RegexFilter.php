<?php
namespace Zodream\Domain\Filter\Filters;

class RegexFilter extends StringFilter {
    protected $error = '验证失败！';

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
        $this->_option = (array)$option + $this->_defaultOption;
    }
}