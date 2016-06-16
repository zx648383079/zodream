<?php
namespace Zodream\Infrastructure;
/**
 * 第三方接口
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/5/13
 * Time: 11:44
 */
use Zodream\Infrastructure\ObjectExpand\JsonExpand;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;

abstract class ThirdParty extends MagicObject {
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
    
    protected $error;

    public function __construct($config = array()) {
        $this->http = new Http\Http();
        if (empty($config)) {
            $this->set(Config::getValue($this->config));
            return;
        }
        if (array_key_exists($this->config, $config) && is_array($config[$this->config])) {
            $this->set($config[$this->config]);
            return;
        }
        $this->set($config);
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
            $this->error = 'api调用名称错误,不存在的API';
            return false;
        }
        $this->set($args);
        $data = $this->getData(isset($this->apiMap[$name][1]) ? $this->apiMap[$name][1] : array());
        if ($data === false) {
            return false;
        }
        if (!isset($this->apiMap[$name][2]) || strtolower($this->apiMap[$name][2]) == 'get') {
            return $this->httpGet($this->apiMap[$name][0], $data);
        }
        $url = $this->apiMap[$name][0];
        $flag = true;
        if (is_array($url)) {
            if (isset($url[2])) {
                $flag = boolval($url[2]);
            }
            $param = $this->getData($url[1]);
            if ($param === false) {
                return false;
            }
            $url = StringExpand::urlBindValue($url[0], $param);
        }
        return $this->httpPost($url, $data, $flag);
    }

    /**
     * 获取值 根据 #区分必须  $key => $value 区分默认值
     * @param array $keys
     * @return array
     */
    protected function getData($keys = array()) {
        $data = array();
        foreach ((array)$keys as $key => $item) {
            if (!is_integer($key)) {
                $data[$key] = $this->get($key, $item);
                continue;
            }
            if (strpos($item, '#') === 0) {
                $k = substr($item, 1);
                $arg = $this->get($k);
                if (is_null($arg)) {
                    $this->error = $k.' 是必须的!';
                    return false;
                }
                if (!is_array($arg)) {
                    $data[$k] = $arg;
                    continue;
                }
                // 判断 #n:m 
                $k = array_keys($arg)[0];
                $arg = current($arg);
                if (is_null($arg)) {
                    $this->error = $k.' 是必须的!';
                    return false;
                }
                $data[$k] = $arg;
                continue;
            }
            if ($this->has($item)) {
                $data[$item] = $this->get($item);
            }
        }
        return $data;
    }

    protected function xml($xml, $is_array = true) {
        return XmlExpand::decode($xml, $is_array);
    }

    protected function json($json, $is_array = true) {
        return JsonExpand::decode($json, $is_array);
    }
    
    protected function getXml($name, $args = array(), $is_array = true) {
        return $this->xml($this->getByApi($name, $args), $is_array);
    }

    protected function getJson($name, $args = array(), $is_array = true) {
        return $this->json($this->getByApi($name, $args), $is_array);
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

    /**
     * 获取错误信息
     * @return string
     */
    public function getError() {
        return $this->error;
    }
}