<?php
namespace Zodream\Domain\ThirdParty\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/10
 * Time: 14:34
 */
use Zodream\Domain\Response\Redirect;
use Zodream\Domain\Response\RedirectResponse;
use Zodream\Infrastructure\Error\Error;
use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\ThirdParty;

/**
 * Class BaseOAuth
 * @package Zodream\Domain\ThirdParty\OAuth
 *
 * @property string $identity
 * @property string $username
 * @property string $sex
 * @property string #avatar
 */
abstract class BaseOAuth extends ThirdParty {

    public function callback() {
        $state = Request::get('state');
        if (empty($state) || $state != Factory::session()->get('state')) {
            return false;
        }
        $code = Request::get('code');
        if (empty($code)) {
            return false;
        }
        $this->set('code', $code);
    }

    /**
     * 重定向到登录页面
     */
    public function login() {
        $state = StringExpand::randomNumber(7);
        Factory::session()->set('state', $state);
        $this->set('state', $state);
        return $this->redirect('login');
    }
    
    public function redirect($name) {
        return new RedirectResponse($this->getUrl($name));
    }

    /**
     * 获取用户信息
     * @return array
     */
    public abstract function getInfo();
}