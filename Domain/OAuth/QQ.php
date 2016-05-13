<?php
namespace Zodream\Domain\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/10
 * Time: 15:25
 *
 */
class QQ extends BaseOAuth {

    protected $config = 'qq';

    protected $apiMap = array(
        'login' => array(
            'https://graph.qq.com/oauth2.0/authorize',
            array(
                'response_type' => 'code',
                '#client_id',
                '#redirect_uri',
                '#state',
                'scope',
                'display',
                'g_ut'
            )
        ),
        'access' => array(
            'https://graph.qq.com/oauth2.0/token',
            array(
                '#grant_type',
                '#client_id',
                '#client_secret',
                '#code',
                '#redirect_uri'
            )
        ),
        'openid' => array(
            'https://graph.qq.com/oauth2.0/me',
            '#access_token'
        )
    );

    protected function getJson($name, $args = array(), $is_array = true) {
        $json = $this->getByApi($name, $args);
        if (strpos($json, 'callback') !== false) {
            $leftPos = strpos($json, '(');
            $rightPos = strrpos($json, ')');
            $json  = substr($json, $leftPos + 1, $rightPos - $leftPos -1);
        }
        return $this->json($json, $is_array);
    }

    public function login() {

    }

    public function callback() {
        
    }
}