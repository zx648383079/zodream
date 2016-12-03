<?php
namespace Zodream\Service;
/**
* 启动
* 
* @author Jason
* @time 2015-12-19
*/
use Zodream\Domain\Autoload;
use Zodream\Service\Config;
use Zodream\Service\Factory;
use Zodream\Infrastructure\Event\EventManger;

defined('VERSION') || define('VERSION', 'v3');
defined('APP_DIR') || define('APP_DIR', dirname(__DIR__).'/');
defined('APP_CONTROLLER') || define('APP_CONTROLLER', Config::getInstance()->get('app.controller'));
defined('APP_ACTION') || define('APP_ACTION', Config::getInstance()->get('app.action'));
defined('APP_MODEL') || define('APP_MODEL', Config::getInstance()->get('app.model'));
defined('APP_MODULE') || define('APP_MODULE', 'Default');
defined('DEBUG') || define('DEBUG', false);

class Application {

    protected $path;

    public function __construct($path = null) {
        $this->setPath($path);
    }

    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

    public static function main($path = null) {
        return new static($path);
    }

    public function send() {
        Factory::timer()->begin();
        Autoload::getInstance()
            ->setError()
            ->shutDown();
        //Cookie::restore();
        EventManger::getInstance()->run('appRun');
        return Factory::router()->getRoute($this->path)
            ->run()
            ->send();
    }
}