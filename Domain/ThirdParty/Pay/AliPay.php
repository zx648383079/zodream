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
    const SERVICE = 'create_direct_pay_by_user';
    const SERVICE_WAP = 'alipay.wap.trade.create.direct';
    const SERVICE_WAP_AUTH = 'alipay.wap.auth.authAndExecute';
    const SERVICE_APP = 'mobile.securitypay.pay';

    const GATEWAY = 'https://mapi.alipay.com/gateway.do?';
    const GATEWAY_MOBILE = 'http://wappaygw.alipay.com/service/rest.htm?';

    const VERIFY_URL = 'http://notify.alipay.com/trade/notify_query.do?';
    const VERIFY_URL_HTTPS = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';

    // 配置信息在实例化时传入，以下为范例
    private $config = array(
        // 即时到账方式
        'payment_type' => 1,
        // 传输协议
        'transport' => 'http',
        // 编码方式
        'charset' => 'utf-8',
        // 签名方法
        'sign_type' => 'MD5',
        // 证书路径
        'cacert' => './cacert.pem',
        //验签公钥地址
        'public_key_path' => './alipay_public_key.pem',

        'private_key_path' => ''
        // // 支付完成异步通知调用地址
        // 'notify_url' => 'http://'.$_SERVER['HTTP_HOST'].'/order/callback_alipay/notify',
        // // 支付完成同步返回地址
        // 'return_url' => 'http://'.$_SERVER['HTTP_HOST'].'/order/callback_alipay/return',
        // // 支付宝商家 ID
        // 'partner'      => '2088xxxxxxxx',
        // // 支付宝商家 KEY
        // 'key'          => 'xxxxxxxxxxxx',
        // // 支付宝商家注册邮箱
        // 'seller_email' => 'email@domain.com'
    );

    private $is_mobile = FALSE;

    public $service = self::SERVICE;
    public $gateway = self::GATEWAY;

    /**
     * 配置
     * @param $config  array 配置信息
     * @param null $type string 类型  wap app
     */
    public function __construct($config, $type = null) {
        $this->config = array_merge($this->config, (array)$config);

        $this->is_mobile = (($type == 'wap' || $type === true) ? true : false);

        if ($this->is_mobile) {
            $this->gateway = self::GATEWAY_MOBILE;
        }

        if ($type == 'wap' || $type === true) {
            $this->service = self::SERVICE_WAP;
        } elseif ($type == 'app') {
            $this->service = self::SERVICE_APP;
        }
    }

    /**
     * 准备签名参数
     *
     * @param $params <Array>
     *        $params['out_trade_no']     唯一订单编号
     *        $params['subject']
     *        $params['total_fee']
     *        $params['body']
     *        $params['show_url']
     *        $params['anti_phishing_key']
     *        $params['exter_invoke_ip']
     *        $params['it_b_pay']
     *        $params['_input_charset']
     * @return array
     */
    public function prepareParameters($params) {
        $default = array(
            'service' => $this->service,
            'partner' => $this->config['partner'],
            '_input_charset' => trim(strtolower($this->config['charset']))
        );

        if (!$this->is_mobile) {
            $default = array_merge($default, array(
                'payment_type' => $this->config['payment_type'],
                'seller_id' => $this->config['partner'],
                'notify_url' => $this->config['notify_url'],
            ));
            if (isset($this->config['return_url'])) {
                $default['return_url'] = $this->config['return_url'];
            }
        }

        $params = $this->filterSignParameter(array_merge($default, (array)$params));
        ksort($params);
        reset($params);

        return $params;
    }

    /**
     * 生成签名后的请求参数
     * @param $params
     * @return array
     */
    function buildSignedParameters($params) {
        $params = $this->prepareParameters($params);

        $params['sign'] = $this->signParameters($params);
        if ($params['service'] != self::SERVICE_WAP && $params['service'] != self::SERVICE_WAP_AUTH) {
            $params['sign_type'] = $this->signType;
        }

        return $params;
    }

    /**
     * https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.NgdeQA&treeId=59&articleId=103663&docType=1
     * 服务端生成app支付使用的参数以及签名
     * @param $params <Array>
     * @return array
     */
    function buildSignedParametersForApp($params) {
        $params = $this->prepareParameters($params);

        $params['sign'] = urlencode($this->sign($params));
        $params['sign_type'] = 'RSA';

        $paramStr = [];
        foreach ($params as $k => &$param) {
            $param = '"' . $param . '"';
            $paramStr[] = $k . '=' . $param;
        }

        return implode('&', $paramStr);
    }

    /**
     * 准备移动网页支付的请求参数
     *
     * 移动网页支付接口不同，需要先服务器提交一次请求，拿到返回 token 再返回客户端发起真实支付请求。
     * 该方法只完成第一次服务端请求，生成参数后需要客户端另行处理（可调用`buildRequestFormHTML`生成表单提交）。
     *
     * @param $params <Array>
     *        $params['out_trade_no'] 订单唯一编号
     *        $params['subject']      商品标题
     *        $params['total_fee']    支付总费用
     *        $params['merchant_url'] 商品链接地址
     *        $params['req_id']       请求唯一 ID
     *        $params['it_b_pay']     超期时间（秒）
     * @return array
     * @throws FileException
     */
    function prepareMobileTradeData($params) {
        if (!$this->caFile instanceof File || !$this->caFile->exist()) {
            throw new FileException($this->caFile);
        }
        // 不要用 SimpleXML 来构建 xml 结构，因为有第一行文档申明支付宝验证不通过
        $xml_str = '<direct_trade_create_req>' .
            '<notify_url>' . $this->config['notify_url'] . '</notify_url>' .
            '<call_back_url>' . $this->config['return_url'] . '</call_back_url>' .
            '<seller_account_name>' . $this->config['seller_email'] . '</seller_account_name>' .

            '<out_trade_no>' . $params['out_trade_no'] . '</out_trade_no>' .
            '<subject>' . htmlspecialchars($params['subject'], ENT_XML1, 'UTF-8') . '</subject>' .
            '<total_fee>' . $params['total_fee'] . '</total_fee>' .
            '<merchant_url>' . $params['merchant_url'] . '</merchant_url>' .
            (isset($params['it_b_pay']) ? '<pay_expire>' . $params['it_b_pay'] . '</pay_expire>' : '') .
            '</direct_trade_create_req>';

        $request_data = $this->buildSignedParameters(array(
            'service' => $this->service,
            'partner' => $this->config['partner'],
            'sec_id' => $this->signType,
            'format' => 'xml',
            'v' => '2.0',

            'req_id' => $params['req_id'],
            'req_data' => $xml_str
        ));

        $url = $this->gateway;
        $input_charset = trim(strtolower($this->config['input_charset']));

        if (trim($input_charset) != '') {
            $url = $url . "_input_charset=" . $input_charset;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_CAINFO, $this->caFile);//证书地址
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_POST, true); // post传输数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_data);// post传输数据
        $responseText = curl_exec($curl);
        //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);

        if (empty($responseText)) {
            return NULL;
        }

        parse_str($responseText, $responseData);

        if (empty($responseData['res_data'])) {
            return NULL;
        }

        if ($this->config['sign_type'] == '0001') {
            $responseData['res_data'] = $this->rsaDecrypt($responseData['res_data'], $this->config['private_key_path']);
        }

        //token从res_data中解析出来（也就是说res_data中已经包含token的内容）
        $doc = new \DOMDocument();
        $doc->loadXML($responseData['res_data']);
        $responseData['request_token'] = $doc->getElementsByTagName("request_token")->item(0)->nodeValue;

        $xml_str = '<auth_and_execute_req>' .
            '<request_token>' . $responseData['request_token'] . '</request_token>' .
            '</auth_and_execute_req>';

        return array(
            'service' => self::SERVICE_WAP_AUTH,
            'partner' => $this->config['partner'],
            'sec_id' => $this->signType,
            'format' => 'xml',
            'v' => '2.0',
            'req_data' => $xml_str
        );
    }

    /**
     * 支付完成验证返回参数（包含同步和异步）
     *
     * @return bool
     */
    function verifyCallback() {
        $async = empty($_GET);

        $data = $async ? $_POST : $_GET;
        if (empty($data)) {
            return FALSE;
        }

        $signValid = $this->verifyParameters($data, $data["sign"]);
        $notify_id = isset($data['notify_id']) ? $data['notify_id'] : NULL;
        if ($async && $this->is_mobile) {

            //notify_id从decrypt_post_para中解析出来（也就是说decrypt_post_para中已经包含notify_id的内容）
            $doc = new \DOMDocument();
            $doc->loadXML($data['notify_data']);
            $notify_id = $doc->getElementsByTagName('notify_id')->item(0)->nodeValue;
        }
        //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
        $responseTxt = 'true';
        if (!empty($notify_id)) {
            $responseTxt = $this->verifyFromServer($notify_id);
        }
        //验证
        //$signValid的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
        //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
        return $signValid && preg_match("/true$/i", $responseTxt);
    }

    function verifyFromServer($notify_id) {
        $transport = strtolower(trim($this->config['transport']));
        $partner = trim($this->config['partner']);
        $veryfy_url = ($transport == 'https' ? self::VERIFY_URL_HTTPS : self::VERIFY_URL) . "partner=$partner&notify_id=$notify_id";
        $curl = curl_init($veryfy_url);
        curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl, CURLOPT_CAINFO, $this->config['cacert']);//证书地址
        $responseText = curl_exec($curl);
        // var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
        curl_close($curl);
        return $responseText;
    }

    /**
     * RSA解密
     * @param $content string 需要解密的内容，密文
     * @return string 解密后内容，明文
     * @throws FileException
     */
    public function rsaDecrypt($content) {
        if (!$this->privateKeyFile->exist()) {
            throw  new FileException($this->privateKeyFile);
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
}