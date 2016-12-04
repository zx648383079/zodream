<?php
namespace Zodream\Domain\ThirdParty\WeChat;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/22
 * Time: 19:01
 */
use Zodream\Infrastructure\Base\MagicObject;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;
use Zodream\Infrastructure\Http\Request;

/**
 * Class Message
 * @package Zodream\Domain\ThirdParty\WeChat
 *
 * @property string $toUserName
 * @property string $fromUserName
 * @property integer $createTime
 * @property string $msgType
 * //消息
 * @property integer $msgId
 * @property string $msgType
 * //文本
 * @property string $content
 * //图片消息
 * @property string $picUrl
 * @property string $mediaId
 * //语音消息
 * @property string $format
 * @property string $recognition  开通语音识别
 * //视频消息 小视频消息
 * @property string $thumbMediaId
 * //地理位置消息
 * @property float $location_X
 * @property float $location_Y
 * @property integer $scale
 * @property string $label
 * //链接消息
 * @property string $title
 * @property string $description
 * @property string $url
 *
 * @property string $event
 */
class Message extends MagicObject {

    protected $xml;

    protected $events = [];

    protected $token;

    public function __construct($token) {
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
            $args = (array)XmlExpand::decode($this->xml, false);
            foreach ($args as $key => $item) {
                $this->set(lcfirst($key), $item);
            }
        }
        return $this;
    }

    public function getEvent() {
        if (!$this->isEvent()) {
            return false;
        }
        return $this->event;
    }

    public function isEvent() {
        return $this->msgType == 'event';
    }

    public function isMessage() {
        return !$this->isEvent();
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