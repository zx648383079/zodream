<?php
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\Interfaces\FilterObject;

class NumberFilter extends FilterObject {
    protected $error = '不是有效的数字！';

    protected $_defaultOption = array(
        'min' => PHP_INT_MIN,
        'max' => PHP_INT_MAX
    );

    public function filter($arg) {
        if (!is_numeric($arg)) {
            return null;
        }
        if ($this->_option['min'] > $arg) {
            return $this->_option['min'];
        }
        if ($this->_option['max'] < $arg) {
            return $this->_option['max'];
        }
        return $arg;
    }

    public function setOption($option)
    {
        if (is_string($option)) {
            $option = explode('-', $option);
        }
        if (is_array($option)) {
            $this->setOptionWhenArray($option);
        }
    }

    private function setOptionWhenArray(array $option) {
        if (count($option) < 2) {
            $option[] = PHP_INT_MAX;
        }
        sort($option);
        parent::setOption(array(
            'min' => $option[0],
            'max' => $option[1]
        ));
    }
}