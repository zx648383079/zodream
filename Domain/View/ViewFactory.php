<?php
namespace Zodream\Domain\View;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/3
 * Time: 9:48
 */
use Zodream\Infrastructure\Caching\FileCache;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Disk\Directory;
use Zodream\Infrastructure\Disk\File;
use Zodream\Infrastructure\DomainObject\EngineObject;
use Zodream\Infrastructure\Error\FileException;
use Zodream\Infrastructure\MagicObject;

class ViewFactory extends MagicObject {

    /**
     * @var Directory
     */
    protected $directory;

    /**
     * @var EngineObject
     */
    protected $engine;

    protected $configs;

    /**
     * @var FileCache
     */
    protected $cache;
    
    public function __construct() {
        $this->configs = Config::getValue('view', [
            'driver' => null,
            'directory' => APP_DIR.'/UserInterface/'.APP_MODULE,
            'suffix' => '.php'
        ]);
        if (class_exists($this->configs['driver'])) {
            $class = $this->configs['driver'];
            $this->engine = new $class($this);
        }
        $this->cache = new FileCache();
        $this->set('__zd', $this);
    }
    
    public function setDirectory($directory) {
        if (!$directory instanceof Directory) {
            $directory = new Directory($directory);
        }
        $this->directory = $directory;
        return $this;
    }

    /**
     * MAKE VIEW
     * @param string|File $file
     * @param array $data
     * @return string
     * @throws FileException
     * @throws \Exception
     */
    public function make($file, array $data = array()) {
        if (!$file instanceof File) {
            $file = $this->directory->childFile($file.$this->configs['suffix']);
        }
        if (!$file->exist()) {
            throw new FileException($file->getName().' FILE NOT FIND!');
        }
        $data = array_merge($this->get(), $data);
        if (!$this->engine instanceof EngineObject) {
            return (new View($this, $file, $data))->render();
        }
        /** IF HAS ENGINE*/
        $cacheFile = $this->cache->getCacheFile(sha1($file->getName()).'.php');
        if (!$cacheFile->exist() || $cacheFile->modifyTime() < $file->modifyTime()) {
            $this->engine->compiler($file, $cacheFile);
        }
        return (new View($this, $cacheFile, $data))->render();
    }
}