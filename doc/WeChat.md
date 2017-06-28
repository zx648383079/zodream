# 微信公众号开放目录

- [微信公众号](#wechat)
    - [QQ](#oauth-qq)
    - [微信](#oauth-wechat)
    - [微博](#oauth-weibo)
- [微信公众号第三方平台](#platform)
    - [事件推送](#platform-notify)
    - [管理](#platform-manage)
    - [授权登录](#platform-oauth)

<a name="wechat"></a>
## 微信公众号

配置
```PHP
'wechat' => [
    'appId' => '',
    'token' => '',
    'aesKey' => '',
    'secret' => '',
    'redirect_uri' => '',
    'platform' => [  // 第三方平台配置
        'appId' => '',
        'aesKey' => '',
        'token' => '',
        'appSecret' => ''
    ]
]
```

<a name="other-search"></a>
### 搜索


<a name="platform"></a>
## 微信公众号第三方平台


<a name="platform-notify"></a>
### 事件推送

<a name="platform-manage"></a>
### 管理

<a name="platform-oauth"></a>
### 授权登录