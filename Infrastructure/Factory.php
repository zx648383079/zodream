<?php
namespace Zodream\Infrastructure;
/**
 * FACTORY!
 *      EVERYWHERE CAN USE,AND NOT CREATE, AND ALL IS SAME,
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/24
 * Time: 22:57
 */
use Zodream\Domain\Debug\Timer;
use Zodream\Domain\View\ViewFactory;
use Zodream\Infrastructure\Caching\Cache;
use Zodream\Infrastructure\Caching\FileCache;
use Zodream\Infrastructure\DomainObject\RouterObject;
use Zodream\Infrastructure\Error\Error;
use Zodream\Infrastructure\Header\Headers;
use Zodream\Infrastructure\I18n\I18n;
use Zodream\Infrastructure\I18n\PhpSource;
use Zodream\Infrastructure\Session\Session;
use Zodream\Infrastructure\Url\Router;

class Factory {
    
    private static $_instance = [];

    /**
     * GET A INSTANCE BY KEY
     *      IF HAD RETURN HAD, IF NOT CREATE FROM CONFIG OR DEFAULT
     * @param string $key CONFIG'S KEY
     * @param string $default
     * @return object
     * @throws Error\Exception
     */
    public static function getInstance($key, $default = null) {
        if (!array_key_exists($key, static::$_instance)) {
            $class = Config::getValue($key, $default);
            if (is_array($class)) {
                $class = $class['driver'] ?: current($class);
            }
            if (!class_exists($class)) {
                Error::out($class.'CLASS IS NOT EXCITE!', __FILE__, __LINE__);
            }
            static::$_instance[$key] = new $class;
        }
        return static::$_instance[$key];
    }

    /**
     * DO YOU NEED A SESSION , HERE!
     * @return Session
     */
    public static function session() {
        return self::getInstance('session', Session::class);
    }

    /**
     * DO YO WANT TO CACHE MODEL? HERE!
     * @return Cache
     */
    public static function cache() {
        return self::getInstance('cache', FileCache::class);
    }

    /**
     * DO YOU WANT TO SHOW LOCAL LANGUAGE? HERE!
     * @return I18n
     */
    public static function i18n() {
        return self::getInstance('i18n', PhpSource::class);
    }

    /**
     * O! IF YOU NEED ROUTE, HERE. 
     *          IT GO TO DO SOME THING LIKE GO TO CONTROLLER
     * @return Router
     */
    public static function router() {
        return self::getInstance('_____', Router::class);
    }

    /**
     * I WANT TO SEND HEADERS WHEN REQUEST FINISH! 
     *      BUT NOW IT'S NOT FINISH!
     *          WOW! PLEASE WAIT A LITTLE TIME.
     * @return Headers
     */
    public static function header() {
        return self::getInstance('headers', Headers::class);
    }

    /**
     * IT IS MAKE VIEW OR HTML FROM ANT FILES,
     * @return ViewFactory
     */
    public static function view() {
        return self::getInstance('______', ViewFactory::class);
    }

    /**
     * TIMER , LOG ALL TIME
     * @return Timer
     */
    public static function timer() {
        return self::getInstance('____', Timer::class);
    }
}