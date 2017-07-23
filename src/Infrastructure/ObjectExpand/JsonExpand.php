<?php
namespace Zodream\Infrastructure\ObjectExpand;
use Zodream\Infrastructure\Interfaces\JsonAble;

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

    /**
     * @param array|JsonAble $args
     * @param int $option 默认不编码成 多字节 Unicode \u XXX
     * @return string
     */
    public static function encode($args, $option = JSON_UNESCAPED_UNICODE) {
        if ($args instanceof JsonAble) {
            return $args->toJson();
        }
        return json_encode($args, $option);
    }
}