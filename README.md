# ZoDream

[![DUB](https://img.shields.io/dub/l/vibe-d.svg?maxAge=2592000)]()


## 当前版本 v2.3

    相对前面版本变化比较大

## ZoDream 主程序

> UserInterface 用户界面

> Service 服务 不包含任何业务逻辑，主要是定义要完成的任务，为界面提供功能，调用领域实现，

> Domain 领域 负责表达业务概念、业务状态信息、业务规则，包含领域模型

>> Authentication 用户信息处理，包括登录

>> Debug 测试

>> Filter 数据过滤和验证

>> Generate 代码生成

>> Html 对HTML代码的封装，包括Bootstrap部件

>> Image 对图片的处理, 包括二维码、水印、缩略图、验证码、图片对比

>> Response 对浏览器的响应及输出

>> Routing 路由部件

>> Rss 订阅

>> Template 模板语言解析及处理

>> ThirdParty 第三方接口调用

>>> API 第三方实用接口，包括天气、快递、颜值

>>> OAuth 第三方登录接口

>>> SMS 短信接口

> Infrastructure 基础设施 提供实现方法

>> Caching 缓存组件

>> Database 数据库查询组件

>> DomainObject 领域模型基类

>> EventManager 事件管理组件

>> Http 对 Http 访问的集成

>> Mailer 对 PHPmail 简化处理

>> ObjectExpand 基本的数据类型，及其拓展，包括 ENUM Xml Yaml 

>> Request 对各种请求的处理

>> SessionExpand 对会话的拓展组件

>> Traits 其他部件

## 进度

参考 laravel 进行升级，结合 yii 进行完善

### 更新时间：2016-07-16 21:55:00