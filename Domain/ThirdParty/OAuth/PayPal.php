<?php
namespace Zodream\Domain\ThirdParty\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/9/8
 * Time: 9:44
 */
class PayPal extends BaseOAuth {

    const LIVE = 'live';
    const SANDBOX = 'sandbox';

    protected $configKey = 'paypal';

    protected $baseUrl = [
        self::LIVE => 'https://www.paypal.com/',
        self::SANDBOX => 'https://www.sandbox.paypal.com/'
     ];

    protected $apiMap = [
        'login' => [
            'signin/authorize',
            [
                '#client_id',
                'response_type' => 'code',
                'scope' => 'openid profile address email phone https://uri.paypal.com/services/paypalattributes https://uri.paypal.com/services/expresscheckout',
                '#redirect_uri',
                'nonce',
                'state'
            ]
        ],
        'access' => [
            'webapps/auth/protocol/openidconnect/v1/identity/tokenservice',
            [
                'grant_type' => 'authorization_code',
                '#code',
                '#redirect_uri'
            ]
        ],
        'refresh' => [
            'webapps/auth/protocol/openidconnect/v1/identity/tokenservice',
            [
                'grant_type' => 'refresh_token',
                '#refresh_token',
                'scope'
            ]
        ],
        'info' => [
            'webapps/auth/protocol/openidconnect/v1/identity/openidconnect/userinfo',
            [
                'schema' => 'openid',
                '#access_token'
            ]
        ]
    ];

    /**
     *
     * @var bool
     */
    protected $mode = self::LIVE;

    /**
     * IS TEST OR LIVE
     * @param string $arg
     * @return $this
     */
    public function setMode($arg) {
        $this->mode = strtolower($arg) == self::LIVE ? self::LIVE : self::SANDBOX;
        return $this;
    }

    public function getBaseUrl() {
        return $this->baseUrl[$this->mode];
    }


    /**
     * 获取用户信息
     * @return array
     */
    public function getInfo() {
        $user = $this->getJson('info');
        if (!is_array($user) || !array_key_exists('user_id', $user)) {
            return false;
        }
        $user['username'] = $user['name'];
        $user['avatar'] = $user['picture'];
        $user['sex'] = $user['gender'];
        $this->set($user);
        return $user;
    }
}