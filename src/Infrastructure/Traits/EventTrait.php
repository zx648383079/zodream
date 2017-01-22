<?php
namespace Zodream\Infrastructure\Traits;

trait EventTrait {
    /**
     * @var callable[]
     */
    protected $events = [];
    /**
     * @param null $event
     * @param array $args
     * @return $this
     */
    public function invoke($event = null, array $args = null) {
        if (empty($event)) {
            $event = $this->getEvent();
        }
        if (!array_key_exists($event, $this->events)) {
            return $this;
        }
        foreach ($this->events[$event] as $item) {
            if (!is_callable($item)) {
                continue;
            }
            call_user_func_array($item, $args);
        }
        return $this;
    }
}