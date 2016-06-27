<?php
namespace Zodream\Infrastructure\Error;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/27
 * Time: 10:14
 */
class Exception extends \Exception {
    public function getName() {
        return 'Exception';
    }
}