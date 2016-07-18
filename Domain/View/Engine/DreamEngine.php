<?php
namespace Zodream\Domain\View\Engine;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/17
 * Time: 16:36
 */
use Zodream\Domain\Html\Script;
use Zodream\Domain\Routing\Url;
use Zodream\Domain\View\Factory;
use Zodream\Infrastructure\Error\Error;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\ObjectExpand\TimeExpand;
use Zodream\Infrastructure\Traits\ConditionTrait;

class DreamEngine extends PhpEngine {
    /******************** ↓ 自定义视图函数 ↓ *******************************/
    use ConditionTrait;
    
    protected $data = [];

    protected $assetDir = 'assets/';
    
    protected $viewDir = APP_DIR . '/UserInterface/' . APP_MODULE;

    /**
     * 公共部件
     * @var string
     */
    protected $layout = null;

    public function setLayout($name) {
        $this->layout = $name;
    }

    /**
     * 获取视图的路径
     * @param string $file
     * @return string
     */
    public function getView($file) {
        return StringExpand::getFile($this->viewDir, $file).'.php';
    }

    /**
     * 设置资源路径
     * @param string $arg
     */
    public function setAsset($arg) {
        $this->assetDir = $arg.'/';
    }


    /**
     * 在视图中包含其他视图的方法
     * @param string|array $names 视图文件名
     * @param string|array $param 传给视图的内容
     * @param bool|string $replace 是否替换
     */
    public function extend($names, $param = null, $replace = TRUE) {
        if (!$replace) {
            $param = array_merge((array)$this->gain('_extra'), (array)$param);
        }
        $this->set('_extra', $param);
        foreach (ArrayExpand::toFile((array)$names, '/') as $value) {
            $file = $this->getView($value);
            if (is_file($file)) {
                include($file);
            } else {
                Error::out($file.' is not exist', __FILE__, __LINE__);
            }
        }
    }

    /**
     * 输出脚本
     */
    public function jcs() {
        $args   = func_get_args();
        $args[] = $this->gain('_extra', array());
        Script::make(ArrayExpand::sort($args), $this->getAsset());
    }

    /**
     * 添加脚本
     */
    public function script() {
        $args = func_get_args();
        if (empty($args)) {
            return;
        }
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }
        $this->set('_extra', array_merge((array)$this->gain('_extra'), $args));
    }

    /**
     * 输出资源url
     * @param string $file
     */
    public function asset($file) {
        echo Url::toAsset($this->getAsset($file));
    }

    /**
     * 输出完整的网址
     * @param string $url
     * @param array|string $extra
     */
    public function url($url = null, $extra = null) {
        echo Url::to($url, $extra);
    }

    /**
     * 判断是否包含url片段
     * @param string $search
     * @return bool
     */
    public function hasUrl($search = null) {
        return Url::hasUri($search);
    }

    /**
     * 输出执行方法后的数据
     * @param string $func
     */
    public function call($func) {
        $args = array();
        if (func_get_args() > 1) {
            $args = array_slice(func_get_args(), 1);
        }
        echo call_user_func_array($func, $args);
    }

    /**
     * 输出格式化后的时间
     * @param integer|string $time
     */
    public function time($time = null) {
        if (is_null($time)) {
            $time = time();
        }
        echo TimeExpand::format($time);
    }

    /**
     * 输出是多久以前
     * @param int $time
     */
    public function ago($time) {
        if (empty($time)) {
            return;
        }
        echo TimeExpand::isTimeAgo($time);
    }

    /**
     * 翻译 {}
     * @param string $message
     * @param array $param
     * @param string $name
     * @return mixed
     */
    public function t($message, $param = [], $name = 'app') {
        return Factory::i18n()->translate($message, $param, $name);
    }

    /**
     * 直接输出
     * @param string $key
     * @param string $default
     */
    public function out($key, $default = null) {
        echo ArrayExpand::tostring($this->gain($key, $default));
    }

    /**
     * 获取
     * @param string|array $key
     * @param null $default
     * @return array|mixed|string
     */
    public function gain($key, $default = null) {
        if (empty($key)) {
            return $this->data;
        }
        if (!is_array($this->data)) {
            $this->data = (array)$this->_data;
        }
        if ($this->has($key)) {
            return $this->data[$key];
        }
        if (strpos($key, ',') !== false) {
            $result = ArrayExpand::getValues($key, $this->data, $default);
        } else {
            $result = ArrayExpand::getChild($key, $this->data, is_object($default) ? null : $default);
        }
        if (is_callable($default)) {
            return $default($result);
        }
        return $result;
    }

    /**
     * 设置
     * @param string $key
     * @param null $value
     */
    public function set($key, $value = null) {
        if (is_object($key)) {
            $key = (array)$key;
        }
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
            return;
        }
        if (empty($key)) {
            return;
        }
        $this->data[$key] = $value;
    }

    public function has($key = null) {
        if (is_null($key)) {
            return !empty($this->data);
        }
        if (empty($this->data)) {
            return false;
        }
        return array_key_exists($key, $this->data);
    }

    /*********************** ↑ 自定义视图函数 ↑ **************************************
     
    /* 
     * @param null $file
     * @return string
     */
    public function getAsset($file = null) {
        return StringExpand::getFile($this->assetDir, $file);
    }
    
    public function get($path, array $data = []) {
        $this->set($data);
        $content = $this->getContent($path);
        if (!empty($this->layout)) {
            ob_start();
            include $this->getView($this->layout);
            $content = ob_get_contents();
            ob_end_clean();
        }
        return $content;
    }

    protected function getContent($file) {
        ob_start();
        include $this->getView($file);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
}