<?php
namespace Zodream\Domain\Html;
/**
 * 视图组件基类
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/29
 * Time: 16:04
 */
use Zodream\Infrastructure\MagicObject;

abstract class Widget extends MagicObject {
    protected $default = array();
    
    abstract protected function run();
    
    public function __construct() {
        $this->set($this->default);
    }

    /**
     * 视图组件总入口
     * @param array $config
     * @return string
     * @throws \Exception
     */
    public static function show($config = array()) {
        ob_start();
        ob_implicit_flush(false);
        try {
            $instance = new static;
            $instance->set($config);
            $out = $instance->run();
        } catch (\Exception $e) {
            // close the output buffer opened above if it has not been closed already
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }
        return ob_get_clean() . $out;
    }
}