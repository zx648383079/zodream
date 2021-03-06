<?php
namespace Zodream\Infrastructure\Http\Input;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/3
 * Time: 9:29
 */
class Cookie extends BaseInput {
    public function __construct() {
        foreach ($_COOKIE as $key => $value) {
            $this->set(strtolower(str_replace(',_', '', $key)), $this->_clean($value));
        }
    }
}