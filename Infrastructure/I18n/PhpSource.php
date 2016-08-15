<?php
namespace Zodream\Infrastructure\I18n;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/25
 * Time: 17:12
 */
class PhpSource extends I18n {

    public function translate($message, $param = [], $name = 'app') {
        parent::translate($message, $param, $name);
        if (!$this->has($name) || !array_key_exists($message, (array)$this->get($name))) {
            return $this->format($message, $param);
        }
        return $this->format($this->get($name)[$message], $param);
    }


    /**
     * 修改源
     */
    public function reset() {
        if ($this->has($this->fileName)) {
            return;
        }
        $file = $this->directory->childFile($this->language.'/'.$this->fileName.'.php');
        if (!$file->exist()) {
            return;
        }
        $args = include (string)$file;
        if (!is_array($args)) {
            return;
        }
        $this->set($this->fileName, $args);
    }
}