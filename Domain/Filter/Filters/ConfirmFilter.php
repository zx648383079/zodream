<?php
/**
 * Created by PhpStorm.
 * User: Jason
 * Date: 2015/12/25
 * Time: 14:05
 */
namespace Zodream\Domain\Filter\Filters;

use Zodream\Infrastructure\DomainObject\FilterObject;

class ConfirmFilter extends FilterObject {
    protected $error = '两值不相等！';

    public function validate($arg) {
    	$option = $this->_option[0];
    	if (func_num_args() == 2) {
    		$option = func_get_arg(1)[$option];
    	}
        return $arg == $option;
    }

    public function setOption($option) {
        parent::setOption((array)$option);
    }
}