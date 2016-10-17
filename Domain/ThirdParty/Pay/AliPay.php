<?php
namespace Zodream\Domain\ThirdParty\Pay;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/17
 * Time: 15:21
 */
use Zodream\Infrastructure\Error\FileException;
use Zodream\Infrastructure\ObjectExpand\JsonExpand;
use Zodream\Infrastructure\Url\Uri;
use Zodream\Infrastructure\Url\Url;

class AliPay extends BasePay {

    protected $configKey = 'alipay';

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
        ],
        'pay' => [    //app
            'https://openapi.alipay.com/gateway.do',
            [
                '#app_id',
                'method' => 'alipay.trade.app.pay',
                'format' => 'JSON',
                'charset' => 'utf-8',
                'sign_type' => 'RSA',
                'sign',
                '#timestamp', // yyyy-MM-dd HH:mm:ss,
                'version' => '1.0',
                '#notify_url',
                'app_auth_token',
                '#biz_content' => [
                    'body',
                    'scene' => 'bar_code',   //bar_code,wave_code
                    '#auth_code',
                    'discountable_amount',
                    'undiscountable_amount',
                    'extend_params',
                    'royalty_info',
                    'sub_merchant',
                    '#subject',
                    '#out_trade_no',
                    '#total_amount',    // 只能有两位小数
                    'seller_id'
                ]
            ]
        ],
        'appPay' => [    //app
            '',
            [
                '#app_id',
                'method' => 'alipay.trade.app.pay',
                'format' => 'JSON',
                'charset' => 'utf-8',
                'sign_type' => 'RSA',
                'sign',
                '#timestamp', // yyyy-MM-dd HH:mm:ss,
                'version' => '1.0',
                '#notify_url',
                '#biz_content' => [
                    'body',
                    '#subject',
                    '#out_trade_no',
                    'timeout_express',
                    '#total_amount',  // 只能有两位小数
                    'seller_id',
                    'product_code' => 'QUICK_MSECURITY_PAY'
                ]
            ]
        ]
    ];

    /**
     * 加密方法
     * @param string $str
     * @return string
     */
    protected function encrypt($str){
        //AES, 128 模式加密数据 CBC
        $screct_key = base64_decode($this->key);
        $str = trim($str);
        $str = addPKCS7Padding($str);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), 1);
        $encrypt_str =  mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC);
        return base64_encode($encrypt_str);
    }

    /**
     * 解密方法
     * @param string $str
     * @return string
     */
    function decrypt($str){
        //AES, 128 模式加密数据 CBC
        $str = base64_decode($str);
        $screct_key = base64_decode($this->key);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), 1);
        $encrypt_str =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_CBC);
        $encrypt_str = trim($encrypt_str);

        $encrypt_str = stripPKSC7Padding($encrypt_str);
        return $encrypt_str;

    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    protected function addPKCS7Padding($source){
        $source = trim($source);
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);

        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }
    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    protected function stripPKSC7Padding($source){
        $source = trim($source);
        $char = substr($source, -1);
        $num = ord($char);
        if($num==62)return $source;
        $source = substr($source,0,-$num);
        return $source;
    }

    public function rsaEncrypt($data) {
        //转换为openssl格式密钥
        $res = openssl_get_publickey($this->publicKeyFile->read());
        $blocks = $this->splitCN($data, 0, 30, 'utf-8');
        $chrtext  = null;
        $encodes  = array();
        foreach ($blocks as $n => $block) {
            if (!openssl_public_encrypt($block, $chrtext , $res)) {
                echo "<br/>" . openssl_error_string() . "<br/>";
            }
            $encodes[] = $chrtext ;
        }
        $chrtext = implode(',', $encodes);

        return $chrtext;
    }

    public function rsaDecrypt($data, $rsaPrivateKeyPem, $charset) {
        //读取私钥文件
        $priKey = file_get_contents($rsaPrivateKeyPem);
        //转换为openssl格式密钥
        $res = openssl_get_privatekey($priKey);
        $decodes = explode(',', $data);
        $strnull = '';
        $dcyCont = '';
        foreach ($decodes as $n => $decode) {
            if (!openssl_private_decrypt($decode, $dcyCont, $res)) {
                echo '<br/>' . openssl_error_string() . '<br/>';
            }
            $strnull .= $dcyCont;
        }
        return $strnull;
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
        $result = $this->verifyContent($content, $sign);
        if (!$result && strpos($content, '\\/') > 0) {
            $content = str_replace('\\/', '/', $content);
            return $this->verifyContent($content, $sign);
        }
        return $result;
    }

    public function verifyContent($content, $sign) {
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
            if ($this->checkEmpty($item) || $key == 'sign' || $key == 'sign_type') {
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
        $data = [];
        foreach ($_GET as $key => $item) {
            $data[$key] = urldecode($item);
        }
        if (!$this->verify($data, base64_decode($data['sign']))) {
            return false;
        }
        return $data;
    }

    public function getAppPayOrder($arg = array()) {
        $data = $this->getData($this->getMap('appPay')[1], array_merge($this->get(), $arg));
        $data['biz_content'] = $this->getSignContent($data['biz_content']);
        $data['sign'] = $this->sign($data);
        return $data;
    }

    public function getPayUrl($arg = array()) {
        $data = $this->getData($this->getMap('pay')[1], array_merge($this->get(), $arg));
        $data['biz_content'] = $this->getSignContent($data['biz_content']);
        $data['sign'] = $this->sign($data);
        $uri = new Uri();
        return $uri->decode($this->getMap('pay')[0])->setData($data);
    }
}