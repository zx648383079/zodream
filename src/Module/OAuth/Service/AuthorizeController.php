<?php
namespace Zodream\Module\OAuth\Service;
use Zodream\Infrastructure\Http\Component\Uri;
use Zodream\Infrastructure\Http\Request;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/12/26
 * Time: 15:02
 */
class AuthorizeController extends Controller {

    public function indexAction(
        $response_type,
        $client_id,
        $redirect_uri,
        $scope = null,
        $state = null) {
        if ($response_type != 'code') {
            return;
        }

        $code = StringExpand::random();
        $uri = new Uri($redirect_uri);
        return $this->redirect($uri->addData([
            'code' => $code,
            'state' => $state
        ]));
    }

    public function tokenAction() {
        $data = Request::post('grant_type,code,redirect_uri,client_id');
        if ($data['grant_type'] !== 'authorization_code') {
            return;
        }

        return $this->ajax([
            'access_token' => $token,
            'token_type' => '',
            'expires_in' => 3600,
            'refresh_token' => '',
            //'scope'
        ]);
    }

    public function refreshAction() {
        $data = Request::post('grant_type,refresh_token,scope');
        if ($data['grant_type'] !== 'refresh_token') {
            return;
        }
        return $this->ajax([
            'access_token' => $token,
            'token_type' => '',
            'expires_in' => 3600,
            'refresh_token' => '',
            //'scope'
        ]);
    }
}