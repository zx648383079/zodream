<?php
namespace Zodream\Domain\ThirdParty\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/5/13
 * Time: 11:33
 */
class GitHub extends BaseOAuth {

    protected $name = 'github';

    protected $apiMap = array(
       'login' => array(
           'https://github.com/login/oauth/authorize',
           array(
               '#client_id',
               '#redirect_uri',
               '#scope',
               'state',
               'allow_signup'
           )
       ),
        'access' => array(
            'https://github.com/login/oauth/access_token',
            array(
                '#client_id',
                '#client_secret',
                '#code',
                'redirect_uri',
                'state'
            ),
            'post'
        ),
        'info' => array(
            'https://api.github.com/user',
            array(
                '#access_token',
            )
        )
    );

    /**
     * @return array
     */
    public function callback() {
        parent::callback();
        $this->http->setHeader('Accept', 'application/json');
        $access = $this->getJson('access');
        
        $this->set($access);
        return $access;
    }
    
    public function getInfo() {
        $this->http->setHeader('Authorization', 'token OAUTH-TOKEN');
        return $this->getJson('info');
    }
}