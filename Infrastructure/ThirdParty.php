<?php
namespace Zodream\Infrastructure;
/**
 * 第三方接口
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/5/13
 * Time: 11:44
 */
use Zodream\Infrastructure\Http\Curl;
use Zodream\Infrastructure\Http\Http;
use Zodream\Infrastructure\ObjectExpand\JsonExpand;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;
use Zodream\Infrastructure\Url\Uri;

abstract class ThirdParty extends MagicObject {

    const GET = 'GET';
    const POST = 'POST';
    /**
     * @var string config 中标记
     */
    protected $name;

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

    protected $log = array();

    public function __construct($config = array()) {
        $this->http = new Curl();
        if (empty($config)) {
            $this->set(Config::getValue($this->name));
            return;
        }
        if (array_key_exists($this->name, $config) && is_array($config[$this->name])) {
            $this->set($config[$this->name]);
            return;
        }
        $this->set($config);
    }

    /**
     * GET NAME
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    protected function httpGet($url) {
        $args = $this->http->setUrl($url)->get();
        $this->log[] = [$url, self::GET, $args];
        return $args;
    }

    protected function httpPost($url, $data) {
        $args = $this->http->setUrl($url)->post($data);
        $this->log[] = [$url, $data, self::POST, $args];
        return $args;
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed|null|string
     */
    protected function getByApi($name, $args = array()) {
        if (array_key_exists($name, $this->apiMap)){
            throw new \InvalidArgumentException('api调用名称错误,不存在的API');
        }
        $args += $this->get();
        $map = $this->apiMap[$name];
        $url = new Uri();
        if (is_array($map[0])) {
            return $this->httpPost(
                $url->decode($map[0][0])
                    ->addData($this->getData($map[0][1], $args)),
                $this->getData($map[1], $args)
            );
        }
        $url->decode($map[0]);
        if (count($map) != 3 || strtoupper($args[2]) != self::POST) {
            return $this->httpGet($url->addData($this->getData($args[1], $args)));
        }
        return $this->httpPost($url,
            $this->getData($map[1], $args));
    }


    /**
     * GET URL THAT METHOD IS GET
     * @param string $name
     * @return Uri
     */
    protected function getUrl($name) {
        $args = $this->apiMap[$name];
        $uri = new Uri();
        if (is_array($args[0])) {
            return $uri->decode($args[0][0])
                ->addData($this->getData($args[0][1], $this->get()));
        }
        $uri->decode($args[0]);
        if (count($args) != 3 || strtoupper($args[2]) != self::POST) {
            $uri->addData($this->getData($args[1], $this->get()));
        }
        return $uri;
    }

    /**
     * 获取值 根据 #区分必须  $key => $value 区分默认值
     * @param array $keys
     * @param array $args
     * @return array
     */
    protected function getData(array $keys, array $args) {
        $data = [];
        foreach ($keys as $key => $item) {
            if (is_integer($key)) {
                $key = $item;
                $item = null;
            }
            $need = false;
            if (strpos($key, '#') === 0) {
                $key = substr($key, 1);
                $need = true;
            }
            $keyTemp = explode(':', $key, 2);
            if (array_key_exists($keyTemp[0], $args)) {
                $item = $args[$keyTemp[0]];
            }
            if (is_null($item)) {
                if ($need) {
                    throw new \InvalidArgumentException($keyTemp[0].' IS NEED!');
                }
                continue;
            }
            if (count($keyTemp) > 1) {
                $key = $keyTemp[1];
            }
            $data[$key] = $item;
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

    /**
     * GET HTTP LOG
     * @return array
     */
    public function getLog() {
        return $this->log;
    }
}