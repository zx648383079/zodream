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

    /**
     * 设置路径
     * @param string $file
     * @return $this
     */
    public function setFile($file) {
        if (!is_null($file)) {
            $this->file = $file;
        }
        return $this;
    }

    /**
     * 设置行号
     * @param string|integer $line
     * @return $this
     */
    public function setLine($line) {
        if (!is_null($line)) {
            $this->line = $line;
        }
        return $this;
    }
}