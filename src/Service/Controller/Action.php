<?php
namespace Zodream\Service\Controller;


/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/5/22
 * Time: 8:55
 */
use Zodream\Infrastructure\Http\Response;

abstract class Action {

    public function init() { }

    public function prepare() {  }

    /**
     * 其他Action正式执行的入口 允许返回值
     * @return string|Response
     */
    public function run() {}
    
    public function finalize() {  }
}