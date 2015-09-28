# 请求

## 所属命名空间 `App\Lib\Web\WRequest`

## 判断提交方式
		isGet()
		isPost()
		isPut()
		isAjax()
		...

## 获取所传参数
		get()
		post()
		直接参数名
		（其他暂未实现）
		
```php

//默认情况下可以用
use App\App;

App::$request->get();      //获取所有 GET 值
App::$request->get('id');  //获取id，如果不存在返回 null
App::$request->get('id', 1 );  //获取id,如果不存在返回 1 
App::$request->get('id,name');  //获取id,和name, 返回关联数组 array('id' => 1 ,'name' => 'user')
App::$request->get('id:user_id,name:pwd 123');  //获取id,和name,并设置别名和单个默认值, 返回关联数组 array('user_id' => 1 ,'pwd' => 'user') ,如果name不存在则返回 '123'

//post 同理

App::$request->id     获取id,默认为 null
App::$request->id_name     获取id和name,返回关联数组,默认为 null


```