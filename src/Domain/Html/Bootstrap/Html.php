<?php
namespace Zodream\Domain\Html\Bootstrap;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/4
 * Time: 10:58
 */
use Zodream\Infrastructure\Support\Html as BaseHtml;

class Html extends BaseHtml {

    public static function container($content, $full = false) {
        return static::div(
            $content,
            [
                'class' => 'container'.($full ? '-fluid' : null)
            ]
        );
    }
    
    public static function row($content) {
        return static::div($content, [
            'class' => 'row'
        ]);
    }
    
    public static function col($content, $size = 1, $types = ['md']) {
        $class = [];
        foreach ((array)$types as $item) {
            $class[] = 'col-'.$item.'-'.$size;
        }
        return static::div($content, array(
            'class' => implode(' ', $class)
        ));
    }

}