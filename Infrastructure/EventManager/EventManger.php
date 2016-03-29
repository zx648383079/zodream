<?php
namespace Zodream\Infrastructure\EventManager;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/10
 * Time: 9:13
 */
use Zodream\Infrastructure\Traits\SingletonPattern;

class EventManger {
    use SingletonPattern;

    protected $canAble = true;
    protected $events = array(
        'appRun' => null,       //程序启动
        'showView' => null,     //显示视图
    );

    /**
     * 获取已经注册的事件名
     * @return array
     */
    public function getEventName() {
        return array_keys($this->events);
    }

    public function add($event, $class, $function, $file, $priority = 10) {
        if (!$this->canAble) {
            return;
        }
        if (!isset($this->events[$event]) || !($this->events[$event] instanceof Event)) {
            $this->events[$event] = new Event();
        }
        $this->events[$event]->add($class, $function, $file, $priority);
    }

    public function run($event, $args = array()) {
        if (!$this->canAble || !isset($this->events[$event]) || !($this->events[$event] instanceof Event)) {
            return;
        }
        $this->events[$event]->run($args);
    }

    /**
     * @var array [$name => $event]
     */
    protected $actionNames = array();
    /**
     * 删除某个
     * @param string $name 根据名称删除
     */
    public function delete($name) {
        if (!isset($this->actionNames[$name])) {
            return;
        }
        $this->events[$this->actionNames[$name]]->delete($name);
    }
}