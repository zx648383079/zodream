<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;

class IntFilter extends FilterObject {

    protected $_defaultOption = array(
        'min' => PHP_INT_MIN,
        'max' => PHP_INT_MAX
    );

    public function filter($arg) {
        if (!is_numeric($arg)) {
            return null;
        }
        $arg = (int)$arg;
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
            'min' => (int)$option[0],
            'max' => (int)$option[1]
        ));
    }
}