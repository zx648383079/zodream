<?php
namespace Zodream\Domain\Template;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/12
 * Time: 18:56
 */
use Zodream\Infrastructure\Config;

class Twig {

    protected $driver;

    public function __construct() {
        $config = Config::getValue('twig', [
            'template' => APP_DIR.'/UserInterface/'.APP_MODULE,
            'cache' => APP_DIR.'/cache/template'
        ]);
        $loader = new Twig_Loader_Filesystem($config['template']);
        $this->driver = new Twig_Environment($loader, [
            'cache' => $config['cache'],
            'debug' => defined('DEBUG') && DEBUG
        ]);
    }

    /**
     *
     * @param string|array $file
     * @param array $data
     */
    public function render($file, $data = []) {
        $this->driver->render($file, $data);
    }
}