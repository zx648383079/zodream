<?php
namespace Zodream\Domain\ThirdParty\Pay;
/**
 * URL: https://pay.weixin.qq.com/wiki/doc/api/app/app.php?chapter=9_1
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/18
 * Time: 19:07
 */
use Zodream\Infrastructure\Disk\File;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;
use Zodream\Infrastructure\Request;

class WeChat extends BasePay {
    protected $configKey = 'wechat';

    protected $apiMap = [
        'order' => [
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
                '#notify_url',
                '#trade_type',
                'limit_pay',
                'sign'
            ],
            'POST'
        ],
        'pay' => [
            '',
            [
                '#appid',
                '#partnerid',
                '#prepayid',
                'package' => 'Sign=WXPay',
                '#noncestr',
                '#timestamp',
                'sign'
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
        ]
    ];

    /**
     * 加入随机数加入签名
     * @param string $name
     * @param array $args
     * @return array
     */
    protected function getDataByName($name, array $args = array()) {
        $args['nonce_str'] = StringExpand::random(32);
        $data = $this->getData($this->apiMap[$name][1], array_merge($this->get(), $args));
        $data['sign'] = $this->sign($data);
        return $data;
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
                    $this->getDataByName($name, $args), 'xml'
                )
            )
        );
    }

    /**
     * 生成预支付订单
     * @param array $args
     * @return array|bool|mixed|object
     */
    public function getOrder(array $args = array()) {
        $args = $this->xmlPost('order', $args);
        if ($args['return_code'] != 'SUCCESS') {
            return false;
        }
        if (!$this->verify($args, $args['sign'])) {
            return false;
        }
        return $args;
    }

    /**
     * 查询订单
     * @param array $args
     * @return array|bool|mixed|object
     */
    public function queryOrder(array $args = array()) {
        $args = $this->xmlPost('query', $args);
        if ($args['return_code'] != 'SUCCESS') {
            return false;
        }
        if (!$this->verify($args, $args['sign'])) {
            return false;
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
            return false;
        }
        if (!$this->verify($args, $args['sign'])) {
            return false;
        }
        return $args;
    }

    /**
     * APP支付参数
     * @param array $args
     * @return array
     */
    public function pay(array $args = array()) {
        return $this->getDataByName('pay', $args);
    }

    /**
     * 下载对账单
     * @param string|File $file
     * @param array $args
     * @return array
     */
    public function downloadBill($file, array $args = array()) {
        $args = $this->httpPost($this->apiMap['bill'][0], XmlExpand::encode(
            $this->getDataByName('bill', $args), 'xml'
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
            return false;
        }
        if (!$this->verify($args, $args['sign'])) {
            return false;
        }
        return $args;
    }

    /**
     * 退款
     * @param array $args
     * @return array|bool|mixed|object
     */
    public function refundOrder(array $args = array()) {
        $args['nonce_str'] = StringExpand::random(32);
        $data = $this->getData($this->apiMap['query'][1], array_merge($this->get(), $args));
        if (!array_key_exists('transaction_id', $data) &&
            !array_key_exists('out_trade_no', $data)) {
            return false;
        }
        if ($data['total_fee'] < $data['refund_fee']) {
            $data['refund_fee'] = $data['total_fee'];
        }
        $data['sign'] = $this->sign($data);
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
            $this->httpPost($this->apiMap['query'][0],
                XmlExpand::encode($data, 'xml'
                )));
        if ($args['return_code'] != 'SUCCESS') {
            return false;
        }
        if (!$this->verify($args, $args['sign'])) {
            return false;
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
            throw new \InvalidArgumentException('KEY IS NEED');
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
        if ($args['return_code'] != 'SUCCESS') {
            return false;
        }
        if (!$this->verify($args, $args['sign'])) {
            return false;
        }
        return $args;
    }
}