<?php 
/**
* 配置文件模板
* 
* @author Jason
* @time 2015-12-2
*/

return array(
		'app'    => array(                           //网站信息
				'title'      => 'ZoDream',
				'host'       => 'http://localhost/',        //主目录
				'model'      => 'Model',                     //后缀
				'controller' => 'Controller',
				'action'     => 'Action',
		),
		'auth'   => array(
				'driver' => Zodream\Head\Auth\Auth::class,        //用户判断
				'role'   => Zodream\Head\Auth\Comma::class,       //权限判断
				'home'  => 'auth'                             //用户登录主页
		),
		'theme'   => array(                           //视图文件信息
				'dir' => 'default',
				'ext' => '.php'
		),
		'route'  => array(
				'driver'  => Zodream\Head\Route\Yii::class,
				'default' => 'HomeController@indexAction',
				'admin'   => 'AdminController@indexAction'
		),
		'db'     => array(							//MYSQL数据库的信息
				'driver'   => Zodream\Body\Db\Pdo::class,
				'host'     => 'localhost',                //服务器
				'port'     => '3306',						//端口
				'database' => 'test',				//数据库
				'user'     => 'root',						//账号
				'password' => '',					//密码
				'prefix'   => 'zodream_',					//前缀
				'encoding' => 'utf8'					//编码
		),
		'mail'   => array(
				'driver'   => Zodream\Head\Mailer::class,
				'host'     => 'smtp.zodream.cn',
				'port'     => 25,
				'user'     => 'admin@zodream.cn',
				'password' => ''
		),
		'upload' => array(
				'maxsize'   => '',                  //最大上传大小 ，单位kb
				'allowtype' => 'mp3',				//允许上次类型，用‘；’分开
				'savepath'  => 'upload/'               //文件保存路径
		),
		'alias'  => array(
				
		)
);