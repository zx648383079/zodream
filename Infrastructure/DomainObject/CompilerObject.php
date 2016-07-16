<?php
namespace Zodream\Infrastructure\DomainObject;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 10:20
 */
interface CompilerObject {
    /**
     * 根据源文件路径获取编译文件路径
     * @return string
     */
    public function getCompiledPath($path);

    /**
     * 判断是否过期
     *
     * @param  string  $path
     * @return bool
     */
    public function isExpired($path);

    /**
     * 编译文件
     *
     * @param  string  $path
     * @return void
     */
    public function compile($path);
}