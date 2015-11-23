<?php
use App\Lib\Enum\ERoute;
/****************************************************
*配置文件
*
*
*******************************************************/

return array(
	'app'    => array(                           //网站信息
		'title'      => 'ZoDream',
		'host'       => 'http://localhost/',        //主目录
		'mode'        => ERoute::COMMON,                            //URL解析方式
		'model'      => 'Model',                     //后缀
		'controller' => 'Controller',
		'action'     => 'Action',
	),
	'auth'   => 'App\\Lib\\Account',
	'view'   => array(                           //视图文件信息
		'dir' => 'view',
		'ext' => '.php'
	),
	'mysql'  => array(							//MYSQL数据库的信息
		'host'     => 'localhost',                //服务器
		'port'     => '3306',						//端口
		'database' => 'test',				//数据库
		'user'     => 'root',						//账号
		'password' => '',					//密码
		'prefix'   => 'zodream_',					//前缀
		'encoding' => 'utf8'					//编码
	),
	'mail' => array(
		'host'     => 'smtp.zodream.cn',
		'port'     => 25,
		'user'     => 'admin@zodream.cn',
		'password' => ''
	),
	'upload' => array(
		'maxsize'   => '',                  //最大上传大小 ，单位kb
		'allowtype' => 'mp3',				//允许上次类型，用‘；’分开
		'savepath'  => 'upload/'               //文件保存路径
	)
);
