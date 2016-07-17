<?php
/**
 * 配置文件模板
 *
 * @author Jason
 * @time 2015-12-2
 */
$configs = array(
	'app'    => array(                           //网站信息
		'title'      => 'ZoDream',
		'host'       => 'http://localhost/',        //主目录
		'model'      => 'Model',                     //后缀
		'form'       => 'Form',
		'controller' => 'Controller',
		'action'     => 'Action',
	),
	'session' => array(
		'driver' => \Zodream\Infrastructure\Session\Session::class
	),
	'auth'   => array(
		'driver' => Zodream\Domain\Authentication\Auth::class,        //用户判断
		'role'   => \Zodream\Domain\Authentication\Roles::class,       //权限判断
		'home'  => 'account'                             //用户登录主页
	),
	'route'  => array(
		'driver'  => Zodream\Domain\Routing\GraceRouter::class,
		'default' => 'Home@index',                        //注册路由， (?<参数>值) 参数为方法接收的参数 值为正则表达式 或 :num :any
		'generate' => 'Zodream\\Domain\\Generate\\Generate@make'
	),
	'db'     => array(							//MYSQL数据库的信息
		'driver'   => Zodream\Infrastructure\Database\Pdo::class,
		'host'     => 'localhost',                //服务器
		'port'     => '3306',						//端口
		'database' => 'test',					//数据库
		'user'     => 'root',						//账号
		'password' => '',						//密码
		'prefix'   => 'zd_',					//前缀
		'encoding' => 'utf8',					//编码
		'allowCache' => true,                   //是否开启查询缓存
		'cacheLife' => 3600,                      //缓存时间
		'persistent' => false                   //使用持久化连接
	),
	'mail'   => array(
		'driver'   => Zodream\Infrastructure\Mailer::class,
		'host'     => 'smtp.zodream.cn',
		'port'     => 25,
		'user'     => 'admin@zodream.cn',
		'name'     => 'ZoDream', //发送者名字
		'email'    => '',  //发送者邮箱
		'password' => ''
	),
	'verify' => array(
		'length' => 4,
		'width' => 100,
		'height' => 30,
		'fontsize' => 20,
		'font' => 5
	),
	'upload' => array(
		'maxsize'   => '',                  //最大上传大小 ，单位kb
		'allowtype' => 'mp3',				//允许上次类型，用‘；’分开
		'savepath'  => 'upload/'               //文件保存路径
	),
	'safe' => array(
		'log' => '',
		'csrf' => false						//是否使用csrf防止表单注入攻击
	),
	'alias'  => array(
		'Config' => Zodream\Infrastructure\Config::class,
		'Request' => Zodream\Infrastructure\Request::class,
		'Cookie' => Zodream\Infrastructure\Cookie::class
	),
	// 注册事件
	'event' => array(
		'canAble' => true,            //是否启动注册事件
		'appRun' => array(),
		'getRoute' => array(),
		'runController' => array(),
		'showView' => array(),
		'response' => array(),
		'download' => array(),
		'executeSql' => array(),
	)
);

if (defined('APP_MODULE')) {
	$configs['view'] = array(                           //视图文件信息
		'directory' => APP_DIR.'/UserInterface/'.APP_MODULE,
		'suffix' => '.php',
		//'mode' => 'common'                        //普通表示 $value 直接取值， 而设为其他值这是 $this->get() 等方法取值
	);
}
return $configs;