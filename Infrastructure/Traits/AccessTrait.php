<?php
namespace Zodream\Infrastructure\Traits;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/18
 * Time: 22:01
 */
use Zodream\Domain\Access\Auth;
use Zodream\Domain\Response\BaseResponse;
use Zodream\Domain\Routing\Url;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Request;

trait AccessTrait {
    
    use ViewTrait;
    
    /**
     * 此方法主要是为了继承并附加规则
     * @return array
     */
    protected function rules() {
        return [];
    }

    /**
     * 在执行之前做规则验证
     * @param string $action 方法名
     * @return boolean|BaseResponse
     */
    protected function beforeFilter($action) {
        $action = str_replace(APP_ACTION, '', $action);
        foreach ($this->rules() as $key => $item) {
            if ($action === $key) {
                return $this->process($item);
            }
            if (is_integer($key) && is_array($item)) {
                $key = (array)array_shift($item);
                if (in_array($action, $key)) {
                    return $this->process($item);
                }
            }
        }
        return true;
    }

    /**
     * @param string|callable $role
     * @return bool|RedirectResponse
     */
    protected function process($role) {
        if (is_callable($role)) {
            return call_user_func($role);
        }
        if (empty($role)) {
            return true;
        }
        if (is_string($role)) {
            $role = explode(',', $role);
        }
        foreach ((array)$role as $item) {
            if (true !== ($arg = $this->processOne($item))) {
                return $arg;
            }
        }
        return true;
    }

    protected function processOne($role) {
        if ($role === '*') {
            return true;
        }
        if ($role === '?') {
            return Auth::guest() ?: $this->redirect('/');
        }
        if ($role === '@') {
            return !Auth::guest() ?: $this->redirect([Config::getValue('auth.home'), 'ReturnUrl' => Url::to()]);
        }
        if ($role === 'p' || $role === 'post') {
            return Request::isPost() ?: $this->redirect('/', 4, '您不能直接访问此页面！', '400');
        }
        if ($role === '!') {
            return $this->redirect('/', 4, '您访问的页面暂未开放！', '413');
        }
        return true;
    }
}