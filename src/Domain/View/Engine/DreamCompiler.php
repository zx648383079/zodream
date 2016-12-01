<?php
namespace Zodream\Domain\View\Engine;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 10:28
 */

class DreamCompiler extends CompilerEngine {
    
    protected $footer = [];


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