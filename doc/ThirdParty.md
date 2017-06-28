# 第三方接口目录

- [第三方登录](#oauth)
    - [QQ](#oauth-qq)
    - [微信](#oauth-wechat)
    - [微博](#oauth-weibo)
- [第三方支付](#pay)
    - [微信支付](#pay-wechat)
    - [支付宝支付](#pay-alipay)
- [第三方短信](#sms)
    - [阿里大于](#sms-alidayu)
    - [i互亿](#pay-ihuyi)

<a name="oauth"></a>
## 第三方登录
<a name="oauth-qq"></a>
### QQ 

配置
```PHP
'qq' => [
    'client_id' => '',
    'redirect_uri' => '',
    'client_secret' => ''
],
```

跳转网址
```PHP
$oauth = new QQ();
$oauth->login();
```

回调
```PHP
$oauth->callback();
$oauth->getInfo();
```

<a name="oauth-wechat"></a>
### 微信

配置
```PHP
'wechat' => [
    'appid' => '',
    'redirect_uri' =>  ,
    'secret' => ''
],
```

跳转网址
```PHP
$oauth = new WeChat();
$oauth->login()
```

回调
```PHP
$oauth->callback();
$oauth->getInfo();
```

<a name="oauth-weibo"></a>
### 微博

配置
```PHP
'weibo' => [
    'client_id' => ' ',
    'redirect_uri' => '',
    'client_secret' => ''
],
```

跳转网址
```PHP
$oauth = new WeiBo();
$oauth->login();
```

回调
```PHP
$oauth->callback();
$oauth->getInfo();
```

<a name="pay"></a>
## 支付

<a name="pay-wechat"></a>
### 微信APP支付

配置
```PHP
'wechat' => array(
    'appid' => '应用ID',
    'mch_id' => '商户号',
    'device_info' => 'web',
    'notify_url' => '异步回调网址',
    'trade_type' => 'APP',
    'key' => '密钥'
)
```

下订单
```PHP
$pay = new WeChat();
$order = $pay->getOrder([
    'body' => '应用名-商品名',
    'out_trade_no' => 订单号,
    'total_fee' => 1分钱,
    'spbill_create_ip' => IP,
    'time_start' => date('Ymdis')
]);
```

调起支付的参数
```PHP
$pay->pay([
    'timestamp' =>  $model->created_at
])
```

异步回调
```PHP
$pay = new WeChat();
$data = $pay->callback();
if (empty($data)) {
    die($pay->appCallbackReturn([
        'return_code' => 'FAIL',
        'return_msg' => $pay->getError()
    ]));
}
die($pay->appCallbackReturn()); //成功时输出
```

<a name="pay-alipay"></a>
### 支付宝APP支付

配置
```PHP
'alipay' => array(
    'key' => '',
    'privateKeyFile' => '/alipay/rsa_private_key.pem',
    //'publicKeyFile' => '/alipay/alipay_rsa_public_key.pem',
    'publicKey' => '',
    'notify_url' => '',
    'partner' => '商户号',
    'seller_id' => 收款账号
)
```

调起支付的参数
```PHP
$pay = new AliPay();
$pay->getMobilePayOrder([
    'timestamp' => date('Y-m-d H:i:s'),
    'subject' => 标题,
    'out_trade_no' => 订单号,
    'total_fee' => 0.01 元,
    'body' => 介绍
]);
```

异步回调
```PHP
$pay = new AliPay();
$data = $pay->callback();
die('success'); //成功时输出
```

<a name="sms"></a>
## 第三方短信
<a name="sms-alidayu"></a>
### 阿里大于

配置
```PHP
'sms' => array(
    'app_key' => '',
    'secret' => '',
)
```

发送短信
```PHP
$sms = new ALiDaYu();
$sms->send('手机号', '模板id', [模板参数], '签名');
```


<a name="sms-ihuyi"></a>
### i互亿

配置
```PHP
'sms' => array(
    'account' => '账号',
    'password' => '密码',
)
```

发送验证码
```PHP
$sms = new IHuYi();
$sms->set('template', '{code}'); // 设置验证码模板
$sms->sendCode('13412341234', '123456');
```

发送短信
```PHP
$sms = new IHuYi();
$sms->send('13412341234', '');
```

查询余额
```PHP
$sms = new IHuYi();
$sms->balance();
```