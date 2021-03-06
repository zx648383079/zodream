<?php
namespace Zodream\Domain\ThirdParty\WeChat\Platform;


use Zodream\Infrastructure\Http\Component\Uri;
use Zodream\Service\Factory;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Http\Request;

/**
 * 网页授权
 * @package Zodream\Domain\ThirdParty\WeChat\Platform
 * @property string $identity
 * @property string $username
 * @property string $sex
 * @property string $avatar
 */
class OAuth extends BasePlatform {
    protected $apiMap = [
        'login' => [
            'https://open.weixin.qq.com/connect/oauth2/authorize',
            [
                '#appid',
                '#redirect_uri',
                'response_type' => 'code',
                'scope' => 'snsapi_userinfo', // snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地
                'state',
                '#component_appid'
                //'#wechat_redirect'
            ]
        ],
        'access' => [
            'https://api.weixin.qq.com/sns/oauth2/component/access_token',
            [
                '#appid',
                '#component_access_token',
                '#code',
                'grant_type' => 'authorization_code',
                '#component_appid',
            ]
        ],
        'refresh_token' => [
            'https://api.weixin.qq.com/sns/oauth2/component/refresh_token',
            [
                '#appid',
                '#component_access_token',
                '#refresh_token',
                'grant_type' => 'refresh_token',
                '#component_appid',
            ]
        ],
        'info' => [
            'https://api.weixin.qq.com/sns/userinfo',
            [
                '#access_token',
                '#openid',
                'lang' => 'zh_CN'
            ]
        ]
    ];

    /**
     * @return Uri
     */
    public function login() {
        $state = StringExpand::randomNumber(7);
        Factory::session()->set('state', $state);
        $this->set('state', $state);
        return $this->getUrl('login')->setFragment('wechat_redirect');
    }

    public function callback() {
        Factory::log()
            ->info('WECHAT CALLBACK: '.var_export($_GET, true));
        $state = Request::get('state');
        if (empty($state) || $state != Factory::session()->get('state')) {
            return false;
        }
        $code = Request::get('code');
        if (empty($code)) {
            return false;
        }
        $access = $this->getJson('access', [
            'code' => $code
        ]);
        if (!is_array($access) || !array_key_exists('openid', $access)) {
            return false;
        }
        $access['identity'] = $access['openid'];
        $this->set($access);
        return $access;
    }

    public function info() {
        $user = $this->getJson('info');
        if (!is_array($user) || !array_key_exists('nickname', $user)) {
            return false;
        }
        $user['username'] = $user['nickname'];
        $user['avatar'] = $user['headimgurl'];
        $user['sex'] = $user['sex'] == 2 ? '女' : '男';
        $user['identity'] = isset($user['unionid']) ? $user['unionid'] : $user['openid'];
        $this->set($user);
        return $user;
    }

}