<?php
namespace Zodream\Domain\ThirdParty\WeChat;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/22
 * Time: 19:01
 */
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;
use Zodream\Infrastructure\Request;

/**
 * Class Message
 * @package Zodream\Domain\ThirdParty\WeChat
 *
 * @property string $event
 */
class Message extends MagicObject {
    const TEXT = 'text';
    const IMAGE = 'image';
    const VOICE = 'voice';
    const VIDEO = 'video';
    const MUSIC = 'music';

    protected $xml;

    protected $events = [];

    public function __construct() {
        $this->get();
    }

    public function get($key = null, $default = null) {
        if (empty($this->_data)) {
            $this->setData();
        }
        return parent::get($key, $default);
    }

    public function setData() {
        if (empty($this->xml)) {
            $this->xml = Request::input();
        }
        if (!empty($this->xml)) {
            $this->_data = (array)XmlExpand::decode($this->xml, false);
        }

        return $this;
    }

    public function getEvent() {
        return $this->get('event');
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

    public function run($event = null) {
        if (empty($event)) {
            $event = $this->getEvent();
        }
        if (!array_key_exists($event, $this->events)) {
            return null;
        }
        $response = new MessageResponse();
        $response->setFromUseName($this->getTo())
            ->setToUseName($this->getFrom());
        foreach ($this->events[$event] as $item) {
            if (!is_callable($item)) {
                continue;
            }
            call_user_func($item, $this, $response);
        }
        return $response;
    }


}