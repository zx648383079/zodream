<?php
namespace Zodream\Infrastructure;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/17
 * Time: 21:28
 */
class Template extends  MagicObject {
    protected $baseDir;

    protected $beginTag = '{';

    protected $endTag = '}';

    public function __construct($baseDir = null) {
        if (!is_dir($baseDir)) {
            $baseDir = APP_DIR.'/UserInterface/Template';
        }
        $this->setBase($baseDir);
    }

    /**
     * 设置基路径
     * @param string $value
     */
    public function setBase($value) {
        $this->baseDir = rtrim($value, '/') . '/';
    }

    /**
     * 设置标志
     * @param string $beginTag
     * @param string $endTag
     */
    public function setTag($beginTag = '{', $endTag = '}') {
        $this->beginTag = $beginTag;
        $this->endTag = $endTag;
    }

    /**
     * 根据路径获取替换后的内容
     * @param string $file
     * @return bool|mixed
     */
    public function getText($file) {
        $file = $this->baseDir.ltrim($file);
        if (!is_file($file)) {
            return false;
        }
        $content = file_get_contents($file);
        return $this->replaceByArray($content, $this->get());
    }

    /**
     * 根据关联数组替换内容
     * @param string $content
     * @param array $data
     * @param null|string $pre
     * @return string
     */
    public function replaceByArray($content, array $data, $pre = null) {
        /*foreach ($data as $key => $item) {
            if (is_array($item)) {
                $content = $this->replaceByArray($content, $item, $pre.$key.'.');
            } else {
                $content = $this->replace($content, $pre.$key, $item);
            }
        }*/
        $keys = [];
        $values = [];
        foreach ($data as $key => $value) {
            $keys[] = $this->beginTag.$key.$this->endTag;
            $values[] = is_array($value) ? var_export($value, true) : $value;
        }
        return str_ireplace($keys, $values, $content);
    }

    /**
     * 替换单个
     * @param string $content
     * @param string $tag
     * @param string $value
     * @return string
     */
    public function replace($content, $tag, $value) {
        return str_ireplace($this->beginTag.$tag.$this->endTag, $value, $content);
    }
}