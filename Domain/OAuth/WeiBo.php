<?php
namespace Zodream\Domain\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/5/13
 * Time: 14:08
 */
class WeiBo extends BaseOAuth {

    protected $config = 'weibo';
    protected $apiMap = array(
       'login' => array(
           'https://api.weibo.com/oauth2/authorize',
           array(
               '#client_id',
               '#redirect_uri',
               'scope',
               'state',
               'display',
               'forcelogin',
               'language'
           )
       ),
        'access' => array(
            'https://api.weibo.com/oauth2/access_token',
            array(
                '#client_id',
                '#client_secret',
                'grant_type' => 'authorization_code',
                '#code',
                '#redirect_uri'
            ),
            'post'
        ),
        'token' => array(
            'https://api.weibo.com/oauth2/get_token_info',
            '#access_token',
            'post'
        )
    );

    public function login() {

    }

    public function callback() {

    }
}