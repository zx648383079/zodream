<?php
namespace Zodream\Domain\View\Engine;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/16
 * Time: 15:51
 */
use Zodream\Infrastructure\Disk\File;
use Zodream\Infrastructure\Interfaces\EngineObject;
use Twig_Loader_Filesystem;
use Twig_Environment;

class TwigEngine implements EngineObject {

    protected $compiler;
    
    public function __construct() {
        $config = Config::getValue('twig', [
            'template' => APP_DIR.'/UserInterface/'.APP_MODULE,
            'cache' => APP_DIR.'/cache/template'
        ]);
        $loader = new Twig_Loader_Filesystem($config['template']);
        $this->compiler = new Twig_Environment($loader, [
            'cache' => $config['cache'],
            'debug' => defined('DEBUG') && DEBUG
        ]);
    }

    /**
     * 获取内容
     *
     * @param  string $path
     * @param  array $data
     * @return string
     */
    public function get($path, array $data = []) {
        return $this->driver->render($path, $data);
    }

    /**
     * COMPILER FILE TO CACHE FILE
     *
     * @param File $file
     * @param File $cacheFile
     * @return bool
     */
    public function compile(File $file, File $cacheFile)
    {
        // TODO: Implement compile() method.
    }
}