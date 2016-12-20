<?php
namespace Zodream\Domain\ThirdParty\WeChat;

use Zodream\Infrastructure\Base\MagicObject;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;
use Zodream\Infrastructure\Http\Request;
use Zodream\Service\Config;

/**
 * 消息管理
 * @package Zodream\Domain\ThirdParty\WeChat
 *
 * @property string $toUserName
 * @property string $fromUserName
 * @property integer $createTime
 * @property string $msgType
 * //消息
 * @property integer $msgId
 * //@property string //$msgType
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
    protected $configKey = 'wechat';

    protected $xml;

    protected $events = [];

    protected $token;

    protected $aesKey;

    protected $encryptType;

    protected $appId;

    public function __construct(array $config = array()) {
        $config = array_merge(Config::getValue($this->configKey, array(
            'aesKey' => '',
            'appId' => ''
        )), $config);
        $this->token = $config['token'];
        $this->aesKey = $config['aesKey'];
        $this->appId = $config['appId'];
        $this->encryptType = Request::get('encrypt_type');
        $this->get();
    }

    public function get($key = null, $default = null) {
        if (empty($this->_data)) {
            $this->setData();
        }
        return parent::get(lcfirst($key), $default);
    }

    public function setData() {
        if (empty($this->xml)) {
            $this->xml = Request::input();
        }
        if (!empty($this->xml)) {
            $args = $this->getData();
            foreach ($args as $key => $item) {
                $this->set(lcfirst($key), $item);
            }
        }
        return $this;
    }

    public function getEvent() {
        if (!$this->isEvent()) {
            return EventEnum::Message;
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

    protected function getData() {
        $data = (array)XmlExpand::decode($this->xml, false);
        if ($this->encryptType != 'aes') {
            return $data;
        }
        $encryptStr = $data['Encrypt'];
        $aes = new Aes($this->aesKey, $this->appId);
        $this->xml = $aes->decrypt($encryptStr);
        $this->appId = $aes->getAppId();
        return (array)XmlExpand::decode($this->xml, false);
    }

    protected function checkSignature($str = '') {
        $signature = Request::get('signature');
        $signature = Request::get('msg_signature', $signature); //如果存在加密验证则用加密验证段
        $timestamp = Request::get('timestamp');
        $nonce = Request::get('nonce');

        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce, $str);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        return $tmpStr == $signature;
    }

    public function valid() {
        $echoStr = Request::get('echostr');
        if (!is_null($echoStr)) {
            if ($this->checkSignature()) {
                exit($echoStr);
            }
            throw new \Exception('no access');
        }
        $encryptStr = '';
        if (Request::isPost()) {
            $data = (array)XmlExpand::decode($this->xml, false);
            if ($this->encryptType != 'aes') {
                return $data;
            }
            $encryptStr = $data['Encrypt'];
        }
        if (!$this->checkSignature($encryptStr)) {
            throw new \Exception('no access');
        }
        return true;
    }
}