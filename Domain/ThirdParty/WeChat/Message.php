<?php
namespace Zodream\Domain\ThirdParty\WeChat;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/22
 * Time: 19:01
 */
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\Request;

class Message extends MagicObject {
    protected $xml;

    protected $events = [];

    public function __construct() {
        $this->get();
    }

    public function get($key = null, $default = null) {
        if (empty($this->_data)) {
            if (empty($this->xml)) {
                $this->xml = Request::input();
            }
            if (!empty($this->xml)) {
                $this->_data = (array)simplexml_load_string($this->xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            }
        }
        return parent::get($key, $default);
    }

    public function getXml() {
        return $this->xml;
    }

    public function getFrom() {
        return $this->get('FromUserName');
    }

    public function getTo() {
        return $this->get('ToUserName');
    }

    public function on($event, callable $func) {
        if (!array_key_exists($event, $this->events)) {
            $this->events[$event] = [];
        }
        $this->events[$event][] = $func;
        return $this;
    }

    public function run($event) {
        if (!array_key_exists($event, $this->events)) {
            return $this;
        }
        foreach ($this->events[$event] as $item) {
            if (!is_callable($item)) {
                continue;
            }
            $response = new MessageResponse();
            $response->setFromUseName($this->getTo())
                ->setToUseName($this->getFrom());
            call_user_func($item, $this, $response);
        }
        return $this;
    }


}