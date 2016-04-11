<?php
namespace Zodream\Domain\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/10
 * Time: 14:34
 */
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Error;
use Zodream\Infrastructure\Http\Http;
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

abstract class BaseOAuth extends MagicObject {
    /**
     * @var string config 中标记
     */
    protected $config = 'oauth';

    /**
     *
     * ['url']
     * ['url', ['a', '#b', 'c' => 'd']]
     * [[url, ['a'], true], ['b'], 'post']
     * @var array
     */
    protected $apiMap = array();

    /**
     * @var Http
     */
    protected $http;

    public function __construct() {
        $this->http = new Http();
        $this->set(Config::getValue($this->config));
    }

    abstract public function login();

    abstract public function callback();

    protected function objectToArray($args) {
        if (!is_object($args) && !is_array($args)) {
            return $args;
        }
        $arr = array();
        foreach ($args as $k => $v){
            $arr[$k] = $this->objectToArray($v);
        }
        return $arr;
    }

    /**
     * 简单实现json到php数组转换功能
     * @param string $json
     * @return array
     */
    protected function jsonParser($json){
        $json = str_replace('{', '', str_replace('}', '', $json));
        $jsonValue = explode(',', $json);
        $arr = array();
        foreach($jsonValue as $v){
            $jValue = explode(':', $v);
            $arr[str_replace('"', '', $jValue[0])] = (str_replace('"', '', $jValue[1]));
        }
        return $arr;
    }

    protected function httpGet($url, $data = array()) {
        if (ini_get("allow_url_fopen") == "1") {
            return file_get_contents(StringExpand::urlBindValue($url, $data));
        }
        return $this->http->get($url, $data);
    }

    protected function httpPost($url, $data, $flag = 0) {
        if ($flag) {
            return $this->http->post($url, $data);
        }
        return $this->http->setUrl($url)->checkSSL(false)->post(null, $data);
    }

    protected function getByApi($name, $args = array()) {
        if (empty($this->apiMap[$name])){
            Error::out("api调用名称错误,不存在的API: <span style='color:red;'>$name</span>", __FILE__, __LINE__);
        }
        $this->set($args);
        $data = $this->getData(isset($this->apiMap[$name][1]) ? $this->apiMap[$name][1] : array());
        if (!isset($this->apiMap[$name][2]) || strtolower($this->apiMap[$name][2]) == 'get') {
            return $this->httpGet($this->apiMap[$name][0], $data);
        }
        $url = $this->apiMap[$name][0];
        $flag = true;
        if (is_array($url)) {
            $flag = isset($url[2]) ? $url : $flag;
            $url = StringExpand::urlBindValue($url[0], $this->getData($url[1]));
        }
        return $this->httpPost($url, $data, $flag);
    }

    /**
     * 获取值 根据 #区分必须  $key => $value 区分固定
     * @param array $keys
     * @return array
     */
    protected function getData($keys = array()) {
        $data = array();
        foreach ($keys as $key => $item) {
            if (!is_integer($key)) {
                $data[$key] = $item;
                continue;
            }
            if (strpos($item, '#') === 0) {
                $k = substr($item, 1);
                $data[$k] = $this->get($k);
                continue;
            }
            if ($this->has($item)) {
                $data[$item] = $this->get($item);
            }
        }
        return $data;
    }

    /**
     * _call
     * 魔术方法，做api调用转发
     * @param string $name    调用的方法名称
     * @param array $arg      参数列表数组
     * @since 5.0
     * @return array          返加调用结果数组
     */
    public function __call($name, $arg) {
        return $this->getByApi($name, isset($arg[0]) ? $arg[0] : array());
    }
}