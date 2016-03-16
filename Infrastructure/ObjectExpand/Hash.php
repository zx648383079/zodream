<?php
namespace Zodream\Infrastructure\ObjectExpand;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/14
 * Time: 17:23
 */
class Hash {
    public static $round = 10;
    public static function make($value, $option = array()) {
        $cost = isset($option['rounds']) ? $option['rounds'] : self::$round;
        return password_hash($value, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    public static function verify($value, $hash) {
        return password_verify($value, $hash);
    }
}