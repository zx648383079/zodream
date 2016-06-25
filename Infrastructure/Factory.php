<?php
namespace Zodream\Infrastructure;
/**
 * 工厂类分发各种单例模式
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/24
 * Time: 22:57
 */
use Zodream\Infrastructure\Caching\Cache;
use Zodream\Infrastructure\Caching\FileCache;
use Zodream\Infrastructure\I18n\I18n;
use Zodream\Infrastructure\I18n\PhpSource;
use Zodream\Infrastructure\Session\Session;

class Factory {
    
    private static $_instance = [];
    
    public static function getInstance($key, $default = null) {
        if (!array_key_exists($key, static::$_instance)) {
            $class = Config::getValue($key, $default);
            if (is_array($class)) {
                $class = $class['driver'] ?? current($class);
            }
            if (!class_exists($class)) {
                Error::out($class.'CLASS IS NOT EXCITE!', __FILE__, __LINE__);
            }
            static::$_instance[$key] = new $class;
        }
        return static::$_instance[$key];
    }

    /**
     * 会话
     * @return Session
     */
    public static function session() {
        return self::getInstance('session', Session::class);
    }

    /**
     * 缓存
     * @return Cache
     */
    public static function cache() {
        return self::getInstance('cache', FileCache::class);
    }

    /**
     * 多语言
     * @return I18n
     */
    public static function i18n() {
        return self::getInstance('i18n', PhpSource::class);
    }
}