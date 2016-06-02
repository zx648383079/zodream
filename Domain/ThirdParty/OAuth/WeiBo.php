<?php
namespace Zodream\Domain\ThirdParty\OAuth;
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
        ),
        'delete' => array(
            'https://api.weibo.com/oauth2/revokeoauth2',
            '#access_token'
        )
    );

    public function callback() {
        /**
         * access_token
         * expires_in
         * remind_in
         * uid
         */
        $access = $this->getJson('access');
        /**
         * uid	string	授权用户的uid。
         * appkey	string	access_token所属的应用appkey。
         * scope	string	用户授权的scope权限。
         * create_at	string	access_token的创建时间，从1970年到创建时间的秒数。
         * expire_in	string	access_token的剩余时间，单位是秒数。
         */
        $info = $this->getJson('token', $access);
        $access['uid'] = $info['uid'];
        return $access;
    }
}