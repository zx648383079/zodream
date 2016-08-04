<?php
namespace Zodream\Infrastructure\ObjectExpand;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/5/12
 * Time: 17:01
 */
class JsonExpand {
    public static function decode($json, $isArray = true) {
        if (!is_string($json)) {
            return $json;
        }
        if ($isArray === false) {
            return json_decode($json);
        }
        return json_decode($json, true);
    } 
    
    public static function encode(array $args) {
        return json_encode($args);
    }
}