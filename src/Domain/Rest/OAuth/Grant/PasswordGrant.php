<?php
namespace Zodream\Domain\Rest\OAuth\Grant;
use Zodream\Domain\Rest\OAuth\Exception\OAuthServerException;
use Zodream\Infrastructure\Request;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/11/29
 * Time: 15:46
 */
class PasswordGrant extends BaseGrant {

    protected function validateUser() {
        $username = Request::request('username');
        if (is_null($username)) {
            throw OAuthServerException::invalidRequest('username');
        }

        $password = Request::request('password');
        if (is_null($password)) {
            throw OAuthServerException::invalidRequest('password');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier() {
        return 'password';
    }
}