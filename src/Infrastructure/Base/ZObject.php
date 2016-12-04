<?php
namespace Zodream\Infrastructure\Base;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/20
 * Time: 12:36
 */
abstract class ZObject {
    /**
     * @param array|ZObject $args
     * @return $this
     */
    public function parse($args) {
        if ($args instanceof ZObject) {
            $args = $args->toArray();
        }
        foreach ((array)$args as $key => $item) {
            if (property_exists($this, $key)) {
                $this->$key = $item;
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray() {
        return array();
    }
}