<?php
namespace Zodream\Domain\View;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/3
 * Time: 9:19
 */
use Zodream\Infrastructure\Disk\File;
use Zodream\Infrastructure\Error\FileException;
use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\ObjectExpand\TimeExpand;
use Zodream\Infrastructure\Traits\ConditionTrait;

/**
 * Class View
 * @package Zodream\Domain\View
 * @property string $title
 * 
 * @method registerMetaTag($content, $options = array(), $key = null) 
 * @method registerLinkTag($url, $options = array(), $key = null)
 * @method registerCss($css, $key = null)
 * @method registerCssFile($url, $options = array(), $key = null)
 * @method registerJs($js, $position = self::HTML_FOOT, $key = null)
 * @method registerJsFile($url, $options = [], $key = null)
 * @method getAssetFile($file)
 * @method string head()
 * @method string foot()
 * @method start($name)
 * @method stop()
 * @method section($name, $default = null)
 */
class View {

    use ConditionTrait;

    const HTML_HEAD = 'html head';

    const HTML_FOOT = 'html body end';

    const JQUERY_LOAD = 'jquery load';
    const JQUERY_READY = 'jquery ready';

    /**
     * @var File
     */
    protected $file;

    /**
     * @var ViewFactory
     */
    protected $factory;
    
    protected $data;
    
    public function __construct($factory, $file = null, array $data = array()) {
        $this->factory = $factory;
        $this->setData($data)->setFile($file);
    }

    /**
     * SET DATA
     * @param array $data
     * @return $this
     */
    public function setData(array $data) {
        $this->data = $data;
        return $this;
    }

    /**
     * SET FILE
     * @param File|string $file
     * @return $this
     */
    public function setFile($file) {
        if (!$file instanceof File) {
            $file = new File($file);
        }
        $this->file = $file;
        return $this;
    }

    /**
     * 
     * @param callable|null $callback
     * @return string
     * @throws \Exception
     */
    public function render(callable $callback = null) {
        try {
            $contents = $this->renderContent();
            $response = isset($callback) ? call_user_func($callback, $this, $contents) : null;
            return !is_null($response) ? $response : $contents;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    protected function renderContent() {
        if (!$this->file->exist()) {
            throw new FileException($this->file.' NOT EXIST!');
        }
        $obLevel = ob_get_level();
        ob_start();
        extract($this->data, EXTR_SKIP);
        try {
            include $this->file->getFullName();
        } catch (Exception $e) {
            $this->handleViewException($e, $obLevel);
        } catch (Throwable $e) {
            $this->handleViewException(new FatalThrowableError($e), $obLevel);
        }

        return ltrim(ob_get_clean());
    }

    /**
     * Handle a view exception.
     *
     * @param  \Exception  $e
     * @param  int  $obLevel
     * @return void
     *
     * @throws $e
     */
    protected function handleViewException(\Exception $e, $obLevel) {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }
        throw $e;
    }

    /**
     * 输出格式化后的时间
     * @param integer|string $time
     * @return string
     */
    public function time($time = null) {
        if (is_null($time)) {
            return null;
        }
        return TimeExpand::format($time);
    }

    /**
     * 输出是多久以前
     * @param int $time
     * @return string
     */
    public function ago($time) {
        return TimeExpand::isTimeAgo($time);
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

    public function extend($name, $data = array()) {
        foreach ((array)$name as $item) {
            echo $this->factory->render($item, $data);
        }
    }
    
    public function __set($name, $value) {
        $this->factory->set($name, $value);
    }

    public function __get($name) {
        return $this->factory->get($name);
    }
    
    public function __unset($name) {
        $this->factory->delete($name);
    }

    public function __call($name, $arguments) {
        if (method_exists($this->factory, $name)) {
            return call_user_func_array([$this->factory, $name], $arguments);
        }
        throw new \BadMethodCallException($name.' METHOD NOT FIND!');
    }
}