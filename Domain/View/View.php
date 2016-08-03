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

/**
 * Class View
 * @package Zodream\Domain\View
 * @property string $title
 */
class View {

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
            include $this->file;
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
    
    public function __set($name, $value) {
        $this->factory->set($name, $value);
    }
    
    public function __unset($name) {
        $this->factory->delete($name);
    }
}