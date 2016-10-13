<?php
namespace Zodream\Domain\ThirdParty\Pay;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/17
 * Time: 15:22
 */
use Zodream\Domain\ThirdParty\ThirdParty;
use Zodream\Infrastructure\Disk\File;

abstract class BasePay extends ThirdParty  {
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

    public function __construct(array $config = array()) {
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
     * @param array $args
     * @return string
     */
    abstract public function sign(array $args);

    /**
     * @param $args
     * @param $sign
     * @return bool
     */
    abstract public function verify(array $args, $sign);

    /**
     * @return mixed
     */
    abstract public function callback();

    public function xml($xml, $isArray = true) {
        if ($isArray) {
            return json_decode(json_encode(parent::xml($xml, false)), true);
        }
        return parent::xml($xml, $isArray);
    }
}