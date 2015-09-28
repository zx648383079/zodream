# INDEX





## INTRODUCTION

这是属于个人的闲暇之作，自学习PHP以来，学习过不少的PHP框架，但是都用的不顺手，`一定` 是我学艺不精，所以才有了这个作品，之所以称为作品，而不是高大上的框架，就是因为本作太杂乱了，不仅有个人的胡思乱想，也有其他框架的影子……

## STEPS

### FIRST

下载

### SECOND

配置

`index.php`

```php

	define("DEBUG", true);                  //是否开启测试模式


	define('APP_DIR', dirname(__FILE__));
	require_once(APP_DIR."/vendor/autoload.php");
	App::main();

```

 app\cofig\ `config.php`
```php

		'app' => array(                           //网站信息
			'title' => '主页',
			'host' => 'http://c.test:8080/',                          //主目录
			'url' => 0                            //网址解析模式
		),
		'view' =>array(                           //视图文件信息
			'dir' => 'app/view',
			'ext' => '.php'
		),
		'mysql' => array(							//MYSQL数据库的信息
			'host' => 'localhost',                //服务器
            'port' => '3306',						//端口
			'database' => 'wechat',				//数据库
			'user' => 'root',						//账号
			'password' => 'root',					//密码
			'prefix' => 'zx_',					//前缀
			'encoding' => 'utf8'					//编码
		),

```

### THIRD

`app\Controller\HomeController.php`

```php

function indexAction()
{
   $this->show();
}

```

在 `app\view` 下新建 `index.php`

```html

<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width, initial-scale=1"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<title>主页</title>

</head>
<body>
	<p>哈哈</p>
</body>
</html>

```