<?php
namespace Zodream\Infrastructure\DomainObject;
/**
 * 导出文件的接口
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 18:21
 */
interface ExpertObject {
    /**
     * 开始
     * @return mixed
     */
    public function send();
}