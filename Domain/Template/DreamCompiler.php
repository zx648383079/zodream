<?php
namespace Zodream\Domain\Template;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 10:28
 */
use Zodream\Infrastructure\DomainObject\CompilerObject;
use Zodream\Infrastructure\FileSystem;

class DreamCompiler extends Compiler implements CompilerObject {

    protected $path;

    protected $footer = [];

    public function getPath() {
        return $this->path;
    }

    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * 编译文件
     *
     * @param  string $path
     * @return void
     */
    public function compile($path = null) {
        if (!empty($path)) {
            $this->setPath($path);
        }

        $contents = $this->compileString(FileSystem::read($this->getPath()));

        if (!is_null($this->cachePath)) {
            FileSystem::write($this->getCompiledPath($this->getPath()), $contents);
        }
    }

    /**
     * 转化内容
     *
     * @param  string  $value
     * @return string
     */
    public function compileString($value) {
        $result = '';

        $this->footer = [];

        // 将PHP源码按照PHP标记进行分割
        foreach (token_get_all($value) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        // 添加到尾部
        if (count($this->footer) > 0) {
            $result = ltrim($result, PHP_EOL)
                .PHP_EOL.implode(PHP_EOL, array_reverse($this->footer));
        }

        return $result;
    }

    protected function parseToken($token) {
        list($id, $content) = $token;

        if ($id == T_INLINE_HTML) {   // 只转化HTML中的内容
            foreach ($this->compilers as $type) {
                $content = $this->{"compile{$type}"}($content);
            }
        }

        return $content;
    }
}