<?php
namespace Zodream\Domain\Authentication;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/3
 * Time: 11:35
 */
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\Traits\SingletonPattern;

class Roles extends MagicObject {

    use SingletonPattern;

    /**
     * 判断权限是否存在
     * @param array|string $key 可以是数组， 可以需要判断的是 键名 或 值
     * @return bool
     */
    public function hasKeyOrValue($key){
        foreach ((array)$key as $item) {
            if ($this->has($item) || $this->hasValue($item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 判断值是否在中间
     *
     * @param string $value
     * @return bool
     */
    public function hasValue($value) {
        return in_array($value, $this->_data);
    }

    /**
     * 必须包含 id name 字段 或 关联数组
     * 例如：
     * array(
        array(
            'id' => 1,
            'name' => 'edit'
        )
    )
     * @param array $roles
     */
    public static function setRoles(array $roles) {
        if (!is_array(current($roles))) {
            static::getInstance()->set($roles);
            return;
        }
        foreach ($roles as $item) {
            static::getInstance()->set($item['id'], $item['name']);
        }
    }

    /**
     * 判断权限是否存在
     * @param array|string $names 可以是数组， 需要判断的可以是 键名 或 值
     * @return bool
     */
    public static function hasRole($names) {
        return static::getInstance()->hasKeyOrValue($names);
    }
    
    public static function judge($role) {
        return static::hasRole($role);
    }
}