<?php
namespace Zodream\Domain\ThirdParty\Pay;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/17
 * Time: 15:22
 */
use Zodream\Infrastructure\Disk\File;
use Zodream\Infrastructure\Error\FileException;
use Zodream\Infrastructure\ThirdParty;

abstract class BasePay extends ThirdParty {
    const MD5 = 'MD5';
    const RSA = 'RSA';
    const RSA2 = 'RSA2';

    protected $signType = self::MD5;

    protected $key;

    /**
     * @var File
     */
    protected $caFile;

    /**
     * 商户私钥文件路径
     * @var File
     */
    protected $privateKeyFile;

    /**
     * @var File
     */
    protected $publicKeyFile;

    public function __construct(array $config) {
        parent::__construct($config);
        $this->signType = strtoupper($this->get('signType', self::RSA));
        if ($this->has('key')) {
            $this->key = $this->get('key');
        }
        if ($this->has('privateKeyFile')) {
            $this->setPrivateKeyFile($this->get('privateKeyFile'));
        }
        if ($this->has('caFile')) {
            $this->setCaFile($this->get('caFile'));
        }
    }

    public function getKey() {
        return $this->key;
    }

    public function setKey($key) {
        $this->key = $key;
        return $this;
    }

    public function getPrivateKeyFile() {
        return $this->privateKeyFile;
    }

    public function setPrivateKeyFile($file) {
        $this->privateKeyFile = $file instanceof File ? $file : new File($file);
        return $this;
    }

    public function getPublicKeyFile() {
        return $this->publicKeyFile;
    }

    public function setPublicKeyFile($file) {
        $this->publicKeyFile = $file instanceof File ? $file : new File($file);
        return $this;
    }

    public function getCaFile() {
        return $this->caFile;
    }

    public function setCaFile($file) {
        $this->caFile = $file instanceof File ? $file : new File($file);
        return $this;
    }


    /**
     * 生成请求参数的签名
     *
     * @param $params array
     * @return string
     * @throws FileException
     */
     public function sign($params) {
        // 支付宝的签名串必须是未经过 urlencode 的字符串
        // 不清楚为何 PHP 5.5 里没有 http_build_str() 方法
        $paramStr = urldecode(http_build_query($params));
        if ($this->signType == self::MD5) {
            return md5($paramStr . $this->key);
        }
        if ($this->signType != self::RSA
            && $this->signType != self::RSA2) {
            return null;
        }
        if (!$this->privateKeyFile->exist()) {
            throw new FileException($this->privateKeyFile);
        }
        $res = openssl_get_privatekey($this->privateKeyFile->read());
        openssl_sign($paramStr, $sign, $res,
            $this->signType == self::RSA ? OPENSSL_ALGO_SHA1 : OPENSSL_ALGO_SHA256);
        openssl_free_key($res);
        //base64编码
        return base64_encode($sign);
    }

    public function verify($params, $sign) {
        $params = $this->filterSignParameter($params);
        ksort($params);
        reset($params);
        $content = urldecode(http_build_query($params));
        if ($this->signType == self::MD5) {
            return md5($content. $this->key) == $sign;
        }
        if ($this->publicKeyFile->exist()) {
            throw new FileException($this->publicKeyFile);
        }
        //转换为openssl格式密钥
        $res = openssl_get_publickey($this->publicKeyFile->read());
        if(!$res){
            throw new Exception('公钥格式错误');
        }
        //调用openssl内置方法验签，返回bool值
        $result = (bool)openssl_verify($content, base64_decode($sign), $res,
            $this->signType == self::RSA ? OPENSSL_ALGO_SHA1 : OPENSSL_ALGO_SHA256);
        //释放资源
        openssl_free_key($res);

        return $result;
    }

    public function filterSignParameter($params) {
        $result = array();
        foreach ($params as $key => $value) {
            if ($key != 'sign' && $key != 'sign_type' && $value) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}