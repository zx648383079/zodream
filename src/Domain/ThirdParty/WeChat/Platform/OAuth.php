<?php
namespace Zodream\Domain\ThirdParty\WeChat\Platform;

use Zodream\Domain\ThirdParty\WeChat\BaseWeChat;
use Zodream\Infrastructure\Http\Component\Uri;
/**
 * 微信第三方平台开放
 * User: zx648
 * Date: 2017/4/5
 * Time: 19:34
 */
class OAuth extends BaseWeChat {

    protected $apiMap = [
        'login' => [
            'https://mp.weixin.qq.com/cgi-bin/componentloginpage',
            [
                '!component_appid',
                '!pre_auth_code',
                '!redirect_uri'
            ]
        ],
        'token' => [ //json
            'https://api.weixin.qq.com/cgi-bin/component/api_component_token',
            [
                '!component_appid',
                '!component_appsecret',
                '!component_verify_ticket'
            ],
            'POST'
        ],
        'pre_auth_code' => [  //json
            [
                'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode',
                '!component_access_token'
            ],
            '!component_appid',
            'POST'
        ],
        'access_token' => [ //json
            [
                'https://api.weixin.qq.com/cgi-bin/component/api_query_auth',
                '!component_access_token'
            ],
            [
                '!component_appid',
                '!authorization_code'
            ],
            'POST'
        ],
        'refresh_token' => [
            [
                'https:// api.weixin.qq.com /cgi-bin/component/api_authorizer_token',
                '!component_access_token'
            ],
            [
                '!component_appid',
                '!authorizer_appid',
                '!authorizer_refresh_token',
            ],
            'POST'
        ],
        'info' => [
            [
                'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info',
                '!component_access_token'
            ],
            [
                '!component_appid',
                '!authorizer_appid',
            ],
            'POST'
        ],
        'getOption' => [
            [
                'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option',
                '!component_access_token'
            ],
            [
                '!component_appid',
                '!authorizer_appid',
                '!option_name'
            ],
            'POST'
        ],
        'setOption' => [
            [
                'https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option',
                '!component_access_token'
            ],
            [
                '!component_appid',
                '!authorizer_appid',
                '!option_name',
                '!option_value'
            ],
            'POST'
        ],
    ];

    /**
     * 2.获取令牌
     * @return mixed
     * @throws \Exception
     */
    public function getToken() {
        $key = 'WeChatThirdToken';
        if (Factory::cache()->has($key)) {
            return Factory::cache()->get($key);
        }
        $args = $this->getJson('token');
        if (!is_array($args)) {
            throw new \Exception('HTTP ERROR!');
        }
        if (!array_key_exists('component_access_token', $args)) {
            throw new \Exception(isset($args['errmsg']) ? $args['errmsg'] : 'GET ACCESS TOKEN ERROR!');
        }
        Factory::cache()->set($key, $args['component_access_token'], $args['expires_in']);
        return $args['component_access_token'];
    }

    /**
     * 3.获取预授权码
     * @return mixed
     * @throws \Exception
     */
    public function getPreAuthCode() {
        $key = 'WeChatThirdPreAuthCode';
        if (Factory::cache()->has($key)) {
            return Factory::cache()->get($key);
        }
        $args = $this->getJson('pre_auth_code');
        if (!is_array($args)) {
            throw new \Exception('HTTP ERROR!');
        }
        if (!array_key_exists('pre_auth_code', $args)) {
            throw new \Exception(isset($args['errmsg']) ? $args['errmsg'] : 'GET ACCESS TOKEN ERROR!');
        }
        Factory::cache()->set($key, $args['pre_auth_code'], $args['expires_in']);
        return $args['pre_auth_code'];
    }

    /**
     * 4.进入授权页面
     * @return Uri
     */
    public function login() {
        return $this->getUrl('login');
    }

    /**
     * 5.授权返回
     */
    public function callback() {

    }

    public function getAccessToken() {

    }
}