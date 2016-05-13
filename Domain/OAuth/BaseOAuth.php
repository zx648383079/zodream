<?php
namespace Zodream\Domain\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/10
 * Time: 14:34
 */
use Zodream\Domain\Response\Redirect;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\ThirdParty;

abstract class BaseOAuth extends ThirdParty {

    abstract public function login();

    abstract public function callback();

    /**
     * 获取url
     * @param string $name
     * @return bool|string
     */
    protected function getUrl($name) {
        if (!isset($this->apiMap[$name][2]) || strtolower($this->apiMap[$name][2]) !== 'get') {
            return false;
        }
        $data = $this->getData(isset($this->apiMap[$name][1]) ? $this->apiMap[$name][1] : array());
        if ($data === false) {
            return false;
        }
        return StringExpand::urlBindValue($this->apiMap[$name][0], $data);
    }
    
    public function redirect($name) {
        Redirect::to($this->getUrl($name));
    }
}