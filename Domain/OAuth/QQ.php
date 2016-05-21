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
                'grant_type' => 'authorization_code',
                '#client_id',
                '#client_secret',
                '#code',
                '#redirect_uri'
            )
        ),
        // 自动续期
        'refresh' => array(
            'https://graph.qq.com/oauth2.0/token',
            array(
                'grant_type' => 'refresh_token',
                '#client_id',
                '#client_secret',
                '#refresh_token'
            )
        ),
        'openid' => array(
            'https://graph.qq.com/oauth2.0/me',
            '#access_token'
        )
    );

    /**
     * @param string $name
     * @param array $args
     * @param bool $is_array
     * @return array
     */
    protected function getJson($name, $args = array(), $is_array = true) {
        $json = $this->getByApi($name, $args);
        if (strpos($json, 'callback') !== false) {
            $leftPos = strpos($json, '(');
            $rightPos = strrpos($json, ')');
            $json  = substr($json, $leftPos + 1, $rightPos - $leftPos -1);
        }
        return $this->json($json, $is_array);
    }

    /**
     * @return array
     */
    public function callback() {
        /**
         * access_token	授权令牌，Access_Token。
         * expires_in	该access token的有效期，单位为秒。
         * refresh_token
         */
        $access = $this->getJson('access');
        /**
         *
         */
        $openId = $this->getJson('openid', $access);
        $access['openid'] = $openId['openid'];
        return $access;
    }
}