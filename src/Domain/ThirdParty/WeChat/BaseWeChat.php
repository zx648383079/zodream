<?php
namespace Zodream\Domain\ThirdParty\WeChat;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/19
 * Time: 22:27
 */
use Zodream\Domain\ThirdParty\ThirdParty;
use Zodream\Infrastructure\ObjectExpand\JsonExpand;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;

abstract class BaseWeChat extends ThirdParty  {
    protected $configKey = 'wechat';

    /**
     * XML编码
     * @param array $data 数据
     * @return string
     */
    protected function xmlEncode(array $data) {
        return XmlExpand::specialEncode($data);
    }

    protected function jsonEncode(array $data) {
        return JsonExpand::encode($data);
    }

    /**
     * POST URL(BY NAME) DATA (JSON ENCODE ARRAY), THEN JSON DECODE
     * @param string $name
     * @param array $data
     * @return mixed
     */
    protected function jsonPost($name, $data = array()) {
        return $this->json($this->httpPost($this->getUrl($name),
            $this->jsonEncode($data)));
    }

    protected function getData(array $keys, array $args) {
        if (in_array('#access_token', $keys) || in_array('access_token', $keys)) {
            $args['access_token'] = (new AccessToken($args))->getAccessToken();
        }
        return parent::getData($keys, $args);
    }
}