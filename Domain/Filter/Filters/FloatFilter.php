<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 13:58
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;
class FloatFilter extends FilterObject {
    protected $_defaultOption = array(
        'min' => null,
        'max' => null,
    );

    public function filter($arg) {
        if (!is_numeric($arg)) {
            return null;
        }
        $arg = (float)$arg;
        if (null !== $this->_option['min'] && $this->_option['min'] > $arg) {
            return $this->_option['min'];
        }
        if (null !== $this->_option['max'] && $this->_option['max'] < $arg) {
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
            $option[] = null;
        }
        sort($option);
        parent::setOption(array(
            'min' => null === $option[0] ? null : (float)$option[0],
            'max' => null === $option[1] ? null : (float)$option[1]
        ));
    }
}