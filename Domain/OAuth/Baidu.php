<?php
namespace Zodream\Domain\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/10
 * Time: 17:44
 */
class BaiDu extends BaseOAuth {
    protected $config = 'baidu';

    protected $apiMap = array(
        'login' => array(
            'http://openapi.baidu.com/oauth/2.0/authorize',
            array(
                '#client_id',
                'response_type' => 'code',
                '#redirect_uri',
                'scope',
                'state',
                'display',
                'force_login',
                'login_type'
            )
        ),
        'access' => array(
            'https://openapi.baidu.com/oauth/2.0/token',
            array(
                'grant_type' => 'authorization_code',
                '#code',
                '#client_id',
                '#client_secret',
                '#redirect_uri'
            )
        )
    );

    public function login() {

    }

    public function callback() {

    }
}