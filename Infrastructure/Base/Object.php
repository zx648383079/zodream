<?php
namespace Zodream\Infrastructure\Base;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/20
 * Time: 12:36
 */
abstract class Object {
    /**
     * @param array|Object $args
     */
    public function parse($args) {
        if ($args instanceof Object) {
            $args = $args->toArray();
        }
        foreach ((array)$args as $key => $item) {
            if (property_exists($this, $key)) {
                $this->$key = $item;
            }
        }
    }

    /**
     * @return array
     */
    public function toArray() {
        return [];
    }
}