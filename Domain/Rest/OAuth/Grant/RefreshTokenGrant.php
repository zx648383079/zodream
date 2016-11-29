<?php
namespace Zodream\Domain\Rest\OAuth\Grant;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/11/29
 * Time: 15:46
 */
use Zodream\Infrastructure\Request;

class RefreshTokenGrant extends BaseGrant {

    public function refreshToken() {
        $refresh_token = Request::post('refresh_token');

        return [
            'access_token',
            'expires_in',
            'refresh_token'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier() {
        return 'refresh_token';
    }
}