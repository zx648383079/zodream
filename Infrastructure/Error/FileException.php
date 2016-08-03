<?php
namespace Zodream\Infrastructure\Error;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/3
 * Time: 9:37
 */
class FileException extends Exception {
    public function getName() {
        return 'FileException';
    }
}