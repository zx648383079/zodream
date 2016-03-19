<?php
namespace Zodream\Infrastructure\Database;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/19
 * Time: 10:30
 */
use Illuminate\Database\Capsule\Manager as Capsule;
use Zodream\Infrastructure\Config;

class Eloquent {

    /*
     *
  'driver'    => 'mysql',
  'host'      => 'localhost',
  'database'  => 'mffc',
  'username'  => 'root',
  'password'  => 'password',
  'charset'   => 'utf8',
  'collation' => 'utf8_general_ci',
  'prefix'    => ''
     */
    /**
     * 注册
     * @param null $configs
     * @return Capsule
     */
    public static function register($configs = null) {
        if (empty($configs)) {
            $configs = Config::getInstance()->get('eloquent');
        }
        $capsule = new Capsule();
        $capsule->addConnection($configs);
        $capsule->bootEloquent();
        return $capsule;
    }
}