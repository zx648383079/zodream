<?php
/****************************************************
*控制器基类
*
*
*******************************************************/
namespace App\Controller;

use App;
use App\Lib\Lang;
use App\Lib\Validation;
use App\Lib\Auth;

class Controller{
	function __construct()
	{
		App::$data = App::config('App');
		App::$data['lang'] = Lang::$language;
	}
	/**
	* 在执行之前做规则验证
	*
	* @param string $request 方法名
	*/
	function before($func)
	{
		if(isset($this->rules))
		{
			$role = isset($this->rules['*']) ? $this->rules['*'] : '';
			$role = isset($this->rules[$func]) ? $this->rules[$func] : $role;
			
			switch ($role) {
				case '?':
					if(!Auth::guest())
					{
						App::redirect('?c=home');
					}
					break;
				case '1':
					if(Auth::guest())
					{
						App::redirect('?c=auth');
					}
					break;
				default:
					if(!App::role($role))
					{
						App::redirect('?c=auth' , 4 ,'您无权操作！','401');
					}
					break;
			}
			
		}
	}

	/**
	* 验证数据
	*
	* @param $request array 要验证的数据
	* @param $param array  验证的规则
	* @return array
	*/
	function validata($request,$param)
	{
		$_vali = new Validation();
		$result = $_vali->make($request,$param);
		
		if(!$result)
		{
			$result = $_vali->error;
		}
		
		return $result;
	}
	

	/**
	* 传递数据
	*
	* @param string|array $key 要传的数组或关键字
	* @param string $value  要传的值
	*/
	function send($key , $value = "")
	{
		if(empty($value))
		{
			if(is_array($key))
			{
				App::$data = array_merge(App::$data , $key);
			}else{
				App::$data['data'] = $key;	
			}
		}else
		{
			App::$data[$key] = $value;
		}
	}
	

	/**
	* 加载视图
	*
	* @param string $name 视图的文件名
	* @param array $data 要传的数据
	*/
	function show($name = "index",$data = array())
	{
		if(!empty($data))
		{
			App::$data = array_merge(App::$data , $data);
		}
		
		if ( APP_API )
		{
			$this->ajaxJson(App::$data);
		}else{
			
			if (extension_loaded('zlib')) { 
				if (  !headers_sent() AND isset($_SERVER['HTTP_ACCEPT_ENCODING']) && 
					strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) 
				//页面没有输出且浏览器可以接受GZIP的页面 
				{ 
					ob_start('ob_gzhandler'); 
				}else{
					ob_start();
				}
			} 
			header( 'Content-Type:text/html;charset=utf-8' );
			ob_implicit_flush(FALSE);
			App::extend($name);
			ob_end_flush();
			exit;
		}
		
	} 

	/**
	* 返回JSON数据
	*
	* @param $data 要传的值
	* @param string $type 返回类型
	*/
	function ajaxJson($data,$type = 'JSON')
	{
		switch (strtoupper($type)){
			case 'JSON' :
				// 返回JSON数据格式到客户端 包含状态信息
				header('Content-Type:application/json; charset=utf-8');
				exit(json_encode($data));
			case 'XML'  :
				// 返回xml格式数据
				header('Content-Type:text/xml; charset=utf-8');
				exit($this->xml_encode($data));
			case 'JSONP':
				// 返回JSON数据格式到客户端 包含状态信息
				header('Content-Type:application/json; charset=utf-8');
				$handler  =   isset($_GET['callback']) ? $_GET['callback'] : 'jsonpReturn';
				exit($handler.'('.json_encode($data).');');  
			case 'EVAL' :
				// 返回可执行的js脚本
				header('Content-Type:text/html; charset=utf-8');
				exit($data);            
		}
		
		exit;
	}

	/**
	* 数组转XML
	*
	* @param array $data 要转的数组
	* @param string $rootNodeName
	* @param null $xml
	* @return mixed
	*/
	function xml_encode($data, $rootNodeName = 'data', $xml=null)
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
			ini_set ('zend.ze1_compatibility_mode', 0);
		}

		if ($xml == null)
		{
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
		}

		// loop through the data passed in.
		foreach($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = "unknownNode_". (string) $key;
			}

			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z]/i', '', $key);

			// if there is another array found recrusively call this function
			if (is_array($value))
			{
				$node = $xml->addChild($key);
				// recrusive call.
				$this->xml_encode($value, $rootNodeName, $node);
			}
			else
			{
				// add single node.
				$value = htmlentities($value);
				$xml->addChild($key,$value);
			}

		}
		// pass back as string. or simple xml object if you want!
		return $xml->asXML();
	}

	/**
	* 显示图片
	*
	* @param $img
	*/
	function showImg($img)
	{
		header('Content-type:image/png');
		imagepng($img);
		imagedestroy($img);
	}
}