<?php
namespace Zodream\Infrastructure\Url;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/9
 * Time: 19:10
 */
use Zodream\Infrastructure\Request;

class DefaultUri extends Uri {
    public function __construct($url = null) {
        if (!is_null($url)) {
            parent::__construct($url);
            return;
        }

        $this->setPath(Request::get('z') ?: $this->getRouteByUrl());
    }

    protected function getRouteByUrl() {
        $urlParams = explode('.php', Url::getUriWithoutParam());
        list($routes, $args) = $this->_spiltArrayByNumber(explode('/', trim(end($urlParams), '/\\')));
        Request::get(true)->set($args);
        return implode('\\', $routes);
    }

    /**
     * 根据数字值分割数组
     * @param array $routes
     * @return array (routes, values)
     */
    private function _spiltArrayByNumber(array $routes) {
        $values = array();
        for ($i = 0, $len = count($routes); $i < $len; $i++) {
            if (!is_numeric($routes[$i])) {
                continue;
            }
            if (($len - $i) % 2 == 0) {
                // 数字作为分割符,无意义
                $values = array_splice($routes, $i + 1);
                unset($routes[$i]);
            } else {
                $values = array_splice($routes, $i - 1);
            }
            break;
        }
        return array(
            $routes,
            $this->_pairValues($values)
        );
    }

    /**
     * 将索引数组根据单双转关联数组
     * @param $values
     * @return array
     */
    private function _pairValues($values) {
        $args = array();
        for ($i = 0, $len = count($values); $i < $len; $i += 2) {
            if (isset($values[$i + 1])) {
                $args[$values[$i]] = $values[$i + 1];
            }
        }
        return $args;
    }
}