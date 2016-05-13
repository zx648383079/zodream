<?php
namespace Zodream\Domain\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/5/13
 * Time: 11:33
 */
class WeChat extends BaseOAuth {

    protected $config = 'wechat';
    protected $apiMap = array(
       'login' => array(
           'https://open.weixin.qq.com/connect/qrconnect',
           array(
               '#appid',
               '#redirect_uri',
               'response_type' => 'code',
               '#scope',
               'state'
           )
       ),
        'access' => array(
            'https://api.weixin.qq.com/sns/oauth2/access_token',
            array(
                '#appid',
                '#secret',
                '#code',
                'grant_type' => 'authorization_code'
            )
        )
    );

    public function login() {

    }

    public function callback() {

    }
}