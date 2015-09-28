<?php
	/****************************************************
	*配置文件
	*
	*
	*******************************************************/
	
	return array(
		'app' => array(                           //网站信息
			'title' => 'ZoDream',
			'host' => 'http://localhost/',        //主目录
			'url' => 0                            //URL解析方式
		),
		'view' =>array(                           //视图文件信息
			'dir' => 'app/view',
			'ext' => '.php'
		),
		'mysql' => array(							//MYSQL数据库的信息
			'host' => 'localhost',                //服务器
            'port' => '3306',						//端口
			'database' => 'test',				//数据库
			'user' => 'root',						//账号
			'password' => '',					//密码
			'prefix' => 'zodream_',					//前缀
			'encoding' => 'utf8'					//编码
		),
		'upload' => array(
			'maxsize' => '',                  //最大上传大小 ，单位kb
			'allowtype' => 'mp3',				//允许上次类型，用‘；’分开
			'savepath' => 'upload/'               //文件保存路径
		)
	);
