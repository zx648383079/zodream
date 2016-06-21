<?php
namespace Zodream\Domain\ThirdParty\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/21
 * Time: 11:08
 */
class DouBan extends BaseOAuth {

    protected $apiMap = [
        'login' => [
            'https://www.douban.com/service/auth2/auth',
            [
                '#client_id',
                '#redirect_uri',
                'response_type' => 'code',
                'scope',
                'state'
            ]
        ],
        'access' => [
            'https://www.douban.com/service/auth2/token',
            [
                '#client_id',
                '#client_secret',
                '#redirect_uri',
                'grant_type' => 'authorization_code',
                '#code'
            ],
            'post'
        ],
        'refresh' => [
            'https://www.douban.com/service/auth2/token',
            [
                '#client_id',
                '#client_secret',
                '#redirect_uri',
                'grant_type' => 'refresh_token',
                '#refresh_token'
            ],
            'post'
        ],
        'info' => [
            'https://api.douban.com/v2/user/~me'
        ]
    ];

    public function callback() {
        parent::callback();
        $access = $this->getJson('access');
        $this->set($access);
        return $access;
    }

    /**
     * 获取用户信息
     * @return array
     */
    public function getInfo() {
        return $this->getJson('info');
    }
}