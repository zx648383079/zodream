<?php
namespace Zodream\Domain\ThirdParty\OAuth;
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
        ),
        'info' => [
            'https://graph.qq.com/user/get_user_info',
            [
                '#client_id:oauth_consumer_key',
                '#openid',
                '#access_token'
            ]
        ]
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
        $this->set($access);
        return $access;
    }

    /**
     * ret	返回码
    msg	如果ret<0，会有相应的错误信息提示，返回数据全部用UTF-8编码。
    nickname	用户在QQ空间的昵称。
    figureurl	大小为30×30像素的QQ空间头像URL。
    figureurl_1	大小为50×50像素的QQ空间头像URL。
    figureurl_2	大小为100×100像素的QQ空间头像URL。
    figureurl_qq_1	大小为40×40像素的QQ头像URL。
    figureurl_qq_2	大小为100×100像素的QQ头像URL。需要注意，不是所有的用户都拥有QQ的100x100的头像，但40x40像素则是一定会有。
    gender	性别。 如果获取不到则默认返回"男"
    is_yellow_vip	标识用户是否为黄钻用户（0：不是；1：是）。
    vip	标识用户是否为黄钻用户（0：不是；1：是）
    yellow_vip_level	黄钻等级
    level	黄钻等级
    is_yellow_year_vip	标识是否为年费黄钻用户（0：不是； 1：是）
     * @return array|bool
     */
    public function getInfo() {
        $args = $this->getJson('info');
        if ($args['ret'] != 0) {
            return false;
        }
        return $args;
    }
}