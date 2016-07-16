<?php
namespace Zodream\Infrastructure\DomainObject;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 15:20
 */
interface EngineObject {
    /**
     * 获取内容
     *
     * @param  string  $path
     * @param  array   $data
     * @return string
     */
    public function get($path, array $data = []);
}