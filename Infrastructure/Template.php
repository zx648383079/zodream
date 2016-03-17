<?php
namespace Zodream\Infrastructure;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/17
 * Time: 21:28
 */
class Template extends  MagicObject {
    protected $baseDir = APP_DIR.'/UserInterface/Template';

    protected $beginTag = '{';

    protected $endTag = '}';

    public function __construct($baseDir = null) {
        if (is_dir($baseDir)) {
            $this->setBase($baseDir);
        }
    }

    public function setBase($value) {
        $this->baseDir = rtrim($value, '/') . '/';
    }

    public function setTag($beginTag = '{', $endTag = '}') {
        $this->beginTag = $beginTag;
        $this->endTag = $endTag;
    }

    public function getText($file) {
        $file = $this->baseDir.ltrim($file);
        if (!is_file($file)) {
            return false;
        }
        $content = file_get_contents($file);
        return $this->replaceByArray($content, $this->get());
    }

    public function replaceByArray($content, array $data, $pre = null) {
        foreach ($data as $key => $item) {
            if (is_array($item)) {
                $content = $this->replaceByArray($content, $item, $pre.$key.'.');
            } else {
                $content = $this->replace($content, $pre.$key, $item);
            }
        }
        return $content;
    }

    public function replace($content, $tag, $value) {
        return str_ireplace($this->beginTag.$tag.$this->endTag, $value, $content);
    }
}