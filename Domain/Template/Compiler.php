<?php
namespace Zodream\Domain\Template;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 10:24
 */

abstract class Compiler {

    /**
     * 编译缓存文件夹路径
     *
     * @var string
     */
    protected $cachePath;

    public function __construct( $cachePath) {
        $this->cachePath = $cachePath;
    }

    /**
     * 获取编译路径
     *
     * @param  string  $path
     * @return string
     */
    public function getCompiledPath($path) {
        return $this->cachePath.'/'.sha1($path).'.php';
    }

    /**
     * 判断文件是否过期
     *
     * @param  string  $path
     * @return bool
     */
    public function isExpired($path) {
        $compiled = $this->getCompiledPath($path);

        if (! $this->cachePath || ! $this->files->exists($compiled)) {
            return true;
        }
        return filemtime($path) >= filemtime($compiled);
    }
}