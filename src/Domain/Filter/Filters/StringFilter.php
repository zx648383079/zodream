<?php
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\Interfaces\FilterObject;

class StringFilter extends FilterObject {
    protected $error = '不是字符串或长度不对！';

    protected $_defaultOption = array(
        'min' => 0,
        'max' => PHP_INT_MAX
    );

    public function filter($arg) {
        if (is_object($arg) && method_exists($arg, '__toString')) {
            $arg = (string) $arg;
        }
        if (!is_scalar($arg)) {
            return null;
        }
        $arg = (string) $arg;
        if ($this->_option['min'] > strlen($arg)) {
            return null;
        } elseif ($this->_option['max'] < strlen($arg)) {
            return null;
        }
        return $arg;
    }

    public function validate($arg) {
        if (is_object($arg) && method_exists($arg, '__toString')) {
            $arg = (string) $arg;
        }
        return parent::validate($arg);
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
            'min' => (int)$option[0],
            'max' => (int)$option[1]
        ));
    }
}