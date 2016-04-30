<?php
namespace Zodream\Infrastructure;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/29
 * Time: 16:50
 */
class Html {
    /**
     * @var array 无内容的标签
     */
    public static $voidTags = array(
        'area' => 1,
        'base' => 1,
        'br' => 1,
        'col' => 1,
        'command' => 1,
        'embed' => 1,
        'hr' => 1,
        'img' => 1,
        'input' => 1,
        'keygen' => 1,
        'link' => 1,
        'meta' => 1,
        'param' => 1,
        'source' => 1,
        'track' => 1,
        'wbr' => 1
    );

    /**
     * @var array 属性的顺序
     */
    public static $attributeOrder = array(
        'type',
        'id',
        'class',
        'name',
        'value',

        'href',
        'src',
        'action',
        'method',

        'selected',
        'checked',
        'readonly',
        'disabled',
        'multiple',

        'size',
        'maxlength',
        'width',
        'height',
        'rows',
        'cols',

        'alt',
        'title',
        'rel',
        'media'
    );

    public static $dataAttributes = ['data', 'data-ng', 'ng'];

    /**
     * 标签
     * @param $name 标签名
     * @param string $content 内容
     * @param array $options 属性值
     * @return string
     */
    public static function tag($name, $content = '', $options = array()) {
        $html = "<$name" . static::renderTagAttributes($options) . '>';
        return isset(static::$voidTags[strtolower($name)]) ? $html : "{$html}{$content}</{$name}>";
    }

    /**
     * 链接
     * @param $text 显示文字
     * @param string $href 链接
     * @param array $option
     * @return string
     */
    public static function a($text, $href = '#', $option = array()) {
        $option['href'] = $href;
        return static::tag('a', $text, $option);
    }

    /**
     * 图片
     * @param string $src
     * @param array $option
     * @return string
     */
    public static function img($src = '#', $option = array()) {
        $option['src'] = $src;
        return static::tag('img', '', $option);
    }

    /**
     * 表单
     * @param $type
     * @param array $option
     * @return string
     */
    public static function input($type, $option = array()) {
        $option['type'] = $type;
        return static::tag('input', '', $option);
    }

    /**
     * 样式
     * @param $content
     * @param array $options
     * @return string
     */
    public static function style($content, $options = array()) {
        return static::tag('style', $content, $options);
    }

    /**
     * 脚本
     * @param $content
     * @param array $options
     * @return string
     */
    public static function script($content, $options = array()) {
        return static::tag('script', $content, $options);
    }

    public static function renderTagAttributes($attributes) {
        if (count($attributes) > 1) {
            $sorted = array();
            foreach (static::$attributeOrder as $name) {
                if (isset($attributes[$name])) {
                    $sorted[$name] = $attributes[$name];
                }
            }
            $attributes = array_merge($sorted, $attributes);
        }
        $html = '';
        foreach ($attributes as $name => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " $name";
                }
            } elseif (is_array($value)) {
                if (in_array($name, static::$dataAttributes)) {
                    foreach ($value as $n => $v) {
                        if (is_array($v)) {
                            $html .= " $name-$n='" . json_encode($v) . "'";
                        } else {
                            $html .= " $name-$n=\"" . htmlspecialchars($v) . '"';
                        }
                    }
                } elseif ($name === 'class') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . htmlspecialchars(implode(' ', $value)) . '"';
                } elseif ($name === 'style') {
                    if (empty($value)) {
                        continue;
                    }
                    $html .= " $name=\"" . static::encode(static::cssStyleFromArray($value)) . '"';
                } else {
                    $html .= " $name='" . json_encode($value) . "'";
                }
            } elseif ($value !== null) {
                $html .= " $name=\"" .htmlspecialchars($value) . '"';
            }
        }
        return $html;
    }

    /**
     * 合并css样式
     * @param array $style
     * @return null
     */
    public static function cssStyleFromArray(array $style) {
        $result = '';
        foreach ($style as $name => $value) {
            $result .= "$name: $value; ";
        }
        return $result === '' ? null : rtrim($result);
    }
    
    public static function __callStatic($name, $arguments) {
        if (array_key_exists($name, static::$voidTags)) {
            return static::tag($name, '', count($arguments) > 0 ? $arguments[0] : array());
        }
        return static::tag($name,
            count($arguments) > 0 ? $arguments[0] : '',
            count($arguments) > 1 ? $arguments[1] : array()
        );
    }
}