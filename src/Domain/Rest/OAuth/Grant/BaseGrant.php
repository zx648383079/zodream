<?php
namespace Zodream\Domain\Rest\OAuth\Grant;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/11/29
 * Time: 15:47
 */
use Zodream\Domain\Rest\OAuth\Exception\OAuthServerException;
use Zodream\Infrastructure\Request;

abstract class BaseGrant {

    protected function validateClient() {
        list($basicAuthUser, $basicAuthPassword) = $this->getBasicAuthCredentials();
        $clientId = Request::request('client_id', $basicAuthUser);
        if (is_null($clientId)) {
            throw OAuthServerException::invalidRequest('client_id');
        }

        // If the client is confidential require the client secret
        $clientSecret = Request::request('client_secret', $basicAuthPassword);

        // If a redirect URI is provided ensure it matches what is pre-registered
        $redirectUri = Request::request('redirect_uri', null);
    }

    public function validateScopes() {

    }

    protected function getBasicAuthCredentials() {
        $header = Request::header('Authorization');
        if (empty($header)) {
            return [null, null];
        }
        if (is_array($header)) {
            $header = current($header);
        }
        if (strpos($header, 'Basic ') !== 0) {
            return [null, null];
        }
        if (!($decoded = base64_decode(substr($header, 6)))) {
            return [null, null];
        }
        if (strpos($decoded, ':') === false) {
            return [null, null]; // HTTP Basic header without colon isn't valid
        }
        return explode(':', $decoded, 2);
    }

    /**
     * @return string
     */
    abstract public function getIdentifier();
}