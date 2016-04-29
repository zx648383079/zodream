<?php
namespace Zodream\Infrastructure;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/29
 * Time: 16:50
 */
class Html {
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

    public static function tag($name, $content = '', $options = array()) {
        $html = "<$name" . static::renderTagAttributes($options) . '>';
        return isset(static::$voidTags[strtolower($name)]) ? $html : "$html$content</$name>";
    }

    public static function style($content, $options = array()) {
        return static::tag('style', $content, $options);
    }

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

    public static function cssStyleFromArray(array $style) {
        $result = '';
        foreach ($style as $name => $value) {
            $result .= "$name: $value; ";
        }
        return $result === '' ? null : rtrim($result);
    }
}