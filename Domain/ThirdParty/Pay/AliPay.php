<?php
namespace Zodream\Domain\ThirdParty\Pay;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/17
 * Time: 15:21
 */
use Zodream\Infrastructure\Disk\File;
use Zodream\Infrastructure\Error\FileException;

class AliPay extends BasePay {

    protected $apiMap = [
        'query' => [
            'https://openapi.alipay.com/gateway.do',
            [
                '#app_id',
                'method' => 'alipay.trade.query',
                'format' => 'JSON',
                'charset' => 'utf-8',
                'sign_type' => 'RSA',
                'sign',
                '#timestamp', // yyyy-MM-dd HH:mm:ss,
                'version' => '1.0',
                'app_auth_token',
                '#biz_content' => [
                    [
                        'out_trade_no',
                        'trade_no'
                    ]
                ]
            ]
        ]
    ];

    /**
     * RSA解密
     * @param $content string 需要解密的内容，密文
     * @return string 解密后内容，明文
     * @throws FileException
     */
    public function rsaDecrypt($content) {
        if (!$this->privateKeyFile->exist()) {
            throw new FileException($this->privateKeyFile);
        }
        $res = openssl_get_privatekey($this->privateKeyFile->read());
        //用base64将内容还原成二进制
        $content = base64_decode($content);
        //把需要解密的内容，按128位拆开解密
        $result = '';
        for ($i = 0; $i < strlen($content) / 128; $i++) {
            $data = substr($content, $i * 128, 128);
            openssl_private_decrypt($data, $decrypt, $res);
            $result .= $decrypt;
        }
        openssl_free_key($res);
        return $result;
    }

    /**
     * 生成请求参数的签名
     *
     * @param $params array
     * @return string
     * @throws FileException
     */
    public function sign(array $params) {
        $content = $this->getSignContent($params);
        if ($this->signType == self::MD5) {
            return md5($content .$this->key);
        }
        if ($this->signType != self::RSA
            && $this->signType != self::RSA2) {
            return null;
        }
        if (!$this->privateKeyFile->exist()) {
            throw new FileException($this->privateKeyFile);
        }
        $res = openssl_get_privatekey($this->privateKeyFile->read());
        openssl_sign($content, $sign, $res,
            $this->signType == self::RSA ? OPENSSL_ALGO_SHA1 : OPENSSL_ALGO_SHA256);
        openssl_free_key($res);
        //base64编码
        return base64_encode($sign);
    }

    public function verify(array $params, $sign) {
        $content = $this->getSignContent($params);
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

    protected function getSignContent(array $params) {
        ksort($params);
        $args = [];
        foreach ($params as $key => $item) {
            if ($this->checkEmpty($item) || $key == 'sign') {
                continue;
            }
            $args[] = "{$key}={$item}";
        }
        return implode('&', $args);
    }

    /**
     * @return mixed
     */
    public function callback() {
        // TODO: Implement callback() method.
    }
}