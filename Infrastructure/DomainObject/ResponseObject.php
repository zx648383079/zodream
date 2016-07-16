<?php
namespace Zodream\Infrastructure\DomainObject;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 12:11
 */
interface ResponseObject {
    /**
     * 发送响应结果
     * @return boolean
     */
    public function send();
}