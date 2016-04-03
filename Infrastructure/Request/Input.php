<?php
namespace Zodream\Infrastructure\Request;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/3
 * Time: 9:29
 */
class Input extends BaseRequest {

    public function __construct() {
        $this->set('input', file_get_contents('php://input'));
    }

    public function get($name = null, $default = null) {
        return parent::get('input');
    }
}