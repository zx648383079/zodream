<?php
namespace Zodream\Domain\ThirdParty\Pay;
/**
 * URL: https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/18
 * Time: 19:07
 */
use Zodream\Domain\Image\Image;
use Zodream\Domain\ThirdParty\ThirdParty;
use Zodream\Infrastructure\Disk\File;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;
use Zodream\Infrastructure\Http\Request;
use Zodream\Infrastructure\Url\Uri;
use Zodream\Service\Routing\Url;

class WeChat extends BasePay {
    /**
     * EXAMPLE: 
     * 'wechat' => array(
            'appid' => '',
            'mch_id' => '',
            'notify_url' => '',
            'trade_type' => 'APP'
        )
     * @var string
     */
    protected $configKey = 'wechat';

    protected $apiMap = [
        'order' => [ //统一下单
            'https://api.mch.weixin.qq.com/pay/unifiedorder',
            [
                '#appid',
                '#mch_id',
                '#device_info',
                '#nonce_str',
                '#body',
                'detail',
                'attach',
                '#out_trade_no',
                'fee_type',
                '#total_fee',   //以分为单位
                '#spbill_create_ip',
                'time_start',
                'time_expire',
                'goods_tag',
                '#notify_url',  //不能带参数
                '#trade_type',
                'limit_pay',
                'sign'
            ],
            'POST'
        ],
        'pay' => [    //app调起支付参数
            '',
            [
                '#appid',
                '#mch_id:partnerid',
                '#prepay_id:prepayid',
                'package' => 'Sign=WXPay',
                '#noncestr',
                '#timestamp',
                'sign'
            ]
        ],
        'appReturn' => [ //app支付结果通用通知商户处理后同步返回给微信参数：
            '',
            [
                'return_code' => 'SUCCESS',   //SUCCESS/FAIL
                'return_msg' => 'OK'
            ]
        ],
        'query' => [
            'https://api.mch.weixin.qq.com/pay/orderquery',
            [
                '#appid',
                '#mch_id',
                [
                    'transaction_id', // 二选一
                    'out_trade_no'
                ],
                '#nonce_str',
                'sign'
            ],
            'POST'
        ],
        'close' => [
            'https://api.mch.weixin.qq.com/pay/closeorder',
            [
                '#appid',
                '#mch_id',
                '#out_trade_no',
                '#nonce_str',
                'sign'
            ],
            'POST'
        ],
        'refund' => [
            'https://api.mch.weixin.qq.com/secapi/pay/refund',
            [
                '#appid',
                '#mch_id',
                'device_info',
                '#nonce_str',
                'sign',
                [
                    'transaction_id',
                    'out_trade_no',
                ],
                '#out_refund_no',
                '#total_fee',
                '#refund_fee',
                'refund_fee_type',
                '#op_user_id'
            ],
            'POST'
        ],
        'queryRefund' => [
            'https://api.mch.weixin.qq.com/pay/refundquery',
            [
                '#appid',
                '#mch_id',
                'device_info',
                '#nonce_str',
                'sign',
                [
                    'transaction_id',  //四选一
                    'out_trade_no',
                    'out_refund_no',
                    'refund_id'
                ]
            ],
            'POST'
        ],
        'bill' => [
            'https://api.mch.weixin.qq.com/pay/downloadbill',
            [
                '#appid',
                '#mch_id',
                'device_info',
                '#nonce_str',
                'sign',
                '#bill_date',
                'bill_type'
            ]
        ],
        'qrcode' => [ //生成支付二维码
            'weixin：//wxpay/bizpayurl',
            [
                '#appid', // 微信分配的公众账号ID
                '#mch_id',
                '#time_stamp',
                '#nonce_str',
                '#product_id',
                'sign'
            ]
        ],
        'qrCallback' => [ // 二维码支付回调输出返回
            '',
            [
                'return_code' => 'SUCCESS',
                'return_msg',
                '#appid',
                '#mch_id',
                '#nonce_str',
                '#prepay_id',
                'result_code' => 'SUCCESS',
                'err_code_des',
                'sign'
            ]
        ],
        'orderQr' => [  //先生成预支付订单再生成二维码
            'weixin：//wxpay/bizpayurl',
            'sr'
        ],
        'jsapi' => [  // 公众号支付 网页端调起支付API
            '',
            [
                '#appId',
                '#timeStamp',
                '#nonceStr',
                '#package' => [
                    '#prepay_id'
                ],
                'signType' => 'MD5',
                'paySign'
            ]
        ]
    ];

    /**
     * 加入随机数加入签名
     * @param string $name
     * @param array $args
     * @return array|false
     */
    protected function getSignData($name, array $args = array()) {
        $args['noncestr'] = $args['nonce_str'] = StringExpand::random(32);
        $data = parent::getSignData($name, $args);
        return empty($this->error) ? $data : false;
    }

    /**
     * 使用xml 提交
     * @param string $name
     * @param array $args
     * @return array|mixed|object
     */
    protected function xmlPost($name, array $args = array()) {
        return $this->xml(
            $this->httpPost($this->apiMap[$name][0],
                XmlExpand::encode(
                    $this->getSignData($name, $args), 'xml'
                )
            )
        );
    }

    /**
     * 生成预支付订单
     [
        'nonce_str' => '',
        'body' => '',
        'out_trade_no' => ',
        'total_fee' => 1,
        'spbill_create_ip' => '',
        'time_start' => date('Ymdis')
     ]
     * @param array $args
     * @return array|bool|mixed|object
     */
    public function getOrder(array $args = array()) {
        $args = $this->xmlPost('order', $args);
        if ($args['return_code'] != 'SUCCESS') {
            throw new \ErrorException($args['return_msg']);
        }
        if (!$this->verify($args, $args['sign'])) {
            throw new \InvalidArgumentException('数据验签失败！');
        }
        $this->set($args);
        return $args;
    }

    /**
     * 查询订单
     * EXAMPLE:
    [
        'out_trade_no' =>
    ]
     * @param array $args
     * @return array|bool|mixed|object
     */
    public function queryOrder(array $args = array()) {
        $args = $this->xmlPost('query', $args);
        if ($args['return_code'] != 'SUCCESS') {
            throw new \ErrorException($args['return_msg']);
        }
        if (!$this->verify($args, $args['sign'])) {
            throw new \InvalidArgumentException('数据验签失败！');
        }
        return $args;
    }

    /**
     * 关闭订单
     * @param array $args
     * @return array|bool|mixed|object
     */
    public function closeOrder(array $args = array()) {
        $args = $this->xmlPost('close', $args);
        if ($args['return_code'] != 'SUCCESS') {
            throw new \ErrorException($args['return_msg']);
        }
        if (!$this->verify($args, $args['sign'])) {
            throw new \InvalidArgumentException('数据验签失败！');
        }
        return $args;
    }

    /**
     * APP支付参数 异步回调必须输出 appCallbackReturn()
     *
     * @param array $args
     * @return array
     */
    public function pay(array $args = array()) {
        if (!array_key_exists('timestamp', $args)) {
            $args['timestamp'] = time();
        }
        return $this->getSignData('pay', $args);
    }

    public function appCallbackReturn(array $args = array()) {
        return XmlExpand::specialEncode(
            $this->getData($this->apiMap['appReturn'][1],
                array_merge($this->get(), $args)));
    }

    /**
     * 下载对账单
     * @param string|File $file
     * @param array $args
     * @return array
     */
    public function downloadBill($file, array $args = array()) {
        $args = $this->httpPost($this->apiMap['bill'][0], XmlExpand::encode(
            $this->getSignData('bill', $args), 'xml'
        ));
        if (!$file instanceof File) {
            $file = new File($file);
        }
        return $file->write($args);
    }

    /**
     * 查询退款
     * @param array $args
     * @return array|bool|mixed|object
     */
    public function queryRefund(array $args = array()) {
        $args = $this->xmlPost('queryRefund', $args);
        if ($args['return_code'] != 'SUCCESS') {
            throw new \ErrorException($args['return_msg']);
        }
        if (!$this->verify($args, $args['sign'])) {
            throw new \InvalidArgumentException('数据验签失败！');
        }
        return $args;
    }

    /**
     * 退款
     * @param array $args
     * @return array|bool|mixed|object
     */
    public function refundOrder(array $args = array()) {
        $data['sign'] = $this->getSignData('refund', $args);
        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        //curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        //curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/cert.pem');
        //默认格式为PEM，可以注释
        //curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        //curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/private.pem');

        //第二种方式，两个文件合成一个.pem文件
        $this->http->addOption(CURLOPT_SSLCERT, (string)$this->privateKeyFile);
        $args = $this->xml(
            $this->httpPost($this->apiMap['refund'][0],
                XmlExpand::encode($data, 'xml'
                )));
        if ($args['return_code'] != 'SUCCESS') {
            throw new \ErrorException($args['return_msg']);
        }
        if (!$this->verify($args, $args['sign'])) {
            throw new \InvalidArgumentException('数据验签失败！');
        }
        return $args;
    }

    /**
     * 生成签名
     * @param array $args
     * @return string
     */
    public function sign($args) {
        if (empty($this->key)) {
            $this->setError('KEY IS NEED');
        }
        ksort($args);
        reset($args);
        $arg = '';
        foreach ($args as $key => $item) {
            if ($this->checkEmpty($item) || $key == 'sign') {
                continue;
            }
            $arg .= "{$key}={$item}&";
        }
        return strtoupper(md5($arg.'key='.$this->key));
    }

    /**
     * 验证
     * @param array $args
     * @param $sign
     * @return bool
     */
    public function verify(array $args, $sign) {
        return $this->sign($args) === $sign;
    }

    /**
     * 交易完成回调
     * @return mixed
     */
    public function callback() {
        $args = $this->xml(Request::input());
        if (!is_array($args)) {
            throw new \InvalidArgumentException('非法数据');
        }
        if ($args['return_code'] != 'SUCCESS') {
            throw new \ErrorException($args['return_msg']);
        }
        if (!$this->verify($args, $args['sign'])) {
            throw new \InvalidArgumentException('数据验签失败！');
        }
        return $args;
    }

    /**
     * 微信二维码支付
     * @param array $arg
     * @return Image
     */
    public function qrPay(array $arg = array()) {
        $url = new Uri($this->apiMap['qrcode']);
        $url->setData($this->getSignData('qrcode', $arg));
        return $url->qrcode();
    }

    /**
     * https://pay.weixin.qq.com/wiki/doc/api/native.php?chapter=6_4
     * @return array|mixed|object
     */
    public function qrCallback() {
        /*$args = $this->callback();
        if ($args === false) {
            return false;
        }
        $order = $this->getOrder($args);
        if ($order === false) {
            return false;
        }*/
        return XmlExpand::encode($this->getSignData('qrCallback'));
    }

    /**
     * 商户后台系统先调用微信支付的统一下单接口，微信后台系统返回链接参数code_url，商户后台系统将code_url值生成二维码图片
     * @param array $args
     * @return Image
     */
    public function orderQr(array $args = array()) {
        $data = $this->getOrder($args);
        if ($data === false) {
            return false;
        }
        return $this->getUrl('orderQr', $data)->qrcode();
    }

    /**
     * 公众号支付 在微信浏览器里面打开H5网页中执行JS调起支付。接口输入输出数据格式为JSON。
     * https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=7_7&index=6
     * @param array $args
     */
    public function jsPay(array $args = array()) {
        $args['nonce_str'] = StringExpand::random(32);
        $args['timeStamp'] = time();
        $data = $this->getData($this->apiMap['jsapi'][1], array_merge($this->get(), $args));
        $data['package'] = 'prepay_id='.$data['package']['prepay_id'];
        $data['paySign'] = $this->sign($data);
        return $data;

    }
}