<?php
namespace Zodream\Domain\ThirdParty\WeChat;


use Zodream\Service\Factory;

class AccessToken extends BaseWeChat {
    protected $apiMap = [
        'token' => [
            'https://api.weixin.qq.com/cgi-bin/token',
            [
                'grant_type' => 'client_credential',
                '#appid',
                '#secret'
            ]
        ]
    ];

    /**
     * GET ACCESS TOKEN AND SAVE CACHE
     * @return string
     * @throws \HttpException
     */
    public function getAccessToken() {
        $key = 'WeChatToken'.$this->get('appid');
        if (Factory::cache()->has($key)) {
            return Factory::cache()->get($key);
        }
        $args = $this->getJson('token');
        if (!array_key_exists('access_token', $args)) {
            throw new \HttpException(isset($args['errmsg']) ? $args['errmsg'] : 'GET ACCESS TOKEN ERROR!');
        }
        Factory::cache()->set($key, $args['access_token'], $args['expires_in']);
        return $args['access_token'];
    }
}