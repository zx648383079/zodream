<?php
namespace Zodream\Domain\Spider;

use Zodream\Infrastructure\Http\Component\Uri;
use Zodream\Infrastructure\Support\Curl;
use Zodream\Infrastructure\Traits\EventTrait;

class Spider {

    use EventTrait;

    const INIT = 0;
    const BEGIN = 1;
    const SUCCESS = 2;
    const FAILURE = 3;

    /**
     * @var Uri
     */
    protected $url;


    public function __construct($url = null) {
        $this->setUrl($url);
    }

    public function setUrl($url) {
        if (!$url instanceof Uri) {
            $url = new Uri($url);
        }
        $this->url = $url;
        return $this;
    }

    /**
     * @param $event
     * @param callable $callback
     * @return $this
     */
    public function on($event, callable $callback) {
        if (!array_key_exists($event, $this->events)) {
            $this->events[$event] = [];
        }
        $this->events[$event][] = $callback;
        return $this;
    }



    public function getHtml() {
        $curl = new Curl($this->url);
        $this->invoke(self::INIT, [$curl]);
        $html = null;
        try {
            $html = $curl->get();
            $this->invoke(self::SUCCESS, [$html]);
        } catch (\Exception $ex) {
            $this->invoke(self::FAILURE, [$ex]);
        }
        return $html;
    }
}