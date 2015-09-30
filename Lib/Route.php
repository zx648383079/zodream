<?php 
namespace App\Lib;
/*****************************************************
* 路由
*
*
*
********************************************************/
use App;
use App\Lib\Helper\HUrl;

class Route
{
		/**
	 * 加载控制器和视图
	 *
	 * @access globe
	 * @param $c string 控制器的名称
	 * @param $v string 视图所在的方法名
	 */
	public static function load()
	{
		$url = self::get();
		
		$con = ucfirst(strtolower($url[0]));
		$name = 'App\\Controller\\'.$con."Controller";
		$view = strtolower($url[1]);
		if( class_exists($name))
		{
			$controller = new $name();
			
			$controller -> before($view);
			$view .= 'Action';
			if(method_exists($controller,$view))
			{
				$controller->$view();
			}else{
				App::error(0,$view,__FILE__,__LINE__);
			}
		}else{
			App::error(0,$name.$view,__FILE__,__LINE__);
		}
	}
	
	private static function get()
	{
		$key = App::config('app.url');
		if(empty($key))
		{
			$key = 0;
		}
		$url = new Route();
		switch ($key) {
			case 0:
				return $url -> c();
				break;
			case 1:
				return $url -> r();
				break;
			case 2:
				return $url -> u();
				break;
			case 3:
				return $url -> s();
				break;
			case 4:
				return $url -> y();
				break;
			case 5:
				return $url -> p();
				break;
			default:
				return $url -> c();
				break;
		}
	} 
	
	private function c()
	{
		return array(
			isset($_GET['c'])?$_GET['c']:'home',
			isset($_GET['v'])?$_GET['v']:'index'
		);
	}
	
	private function r()
	{
		$result = array(
			'home','index'
		);
		
		if(isset($_GET['r']))
		{
			$arr = explode('/',$_GET['r'],2);
			$result = array(
				empty($arr[0])?'home':$arr[0],	
				empty($arr[1])?'index':$arr[1]
			);
		}
		
		return $result;
	}
	
	private function u()
	{
		$result = array();
		$url = HUrl::request_uri();
		$arr = explode('.php', $url);
		if(count($arr) == 2)
		{
			$arra = explode('/', $arr[1]);
			switch (count($arra)) {
				case 1:
					$result = array('home', 'index');
					break;
				case 2:
					$result = array($arra[1], 'index');
					break;
				default:
					$result = array_splice($arra,1);
					break;
			}
		}else{
			$result = array('home', 'index');
		}
		
		return $result;
	}
	
	private function y()
	{
		$result = array();
		$url = HUrl::request_uri();
		$arr = explode('/', $url);
		switch (count($arr)) {
			case 1:
				$result = array('home', 'index');
				break;
			case 2:
				$result = array($arra[1], 'index');
				break;
			default:
				$result = array_splice($arra,1);
				break;
		}
		
		return $result;
	}
	
	private function p()
	{
		$url = HUrl::request_uri();
		preg_match($preg , $url , $result);
		return $result;
	}
	
	private function s()
	{
		$key = '*';
		if(isset($_GET['s']))
		{
			$key  = $_GET['s'];
		}else{
			$url = HUrl::request_uri();
			$ar = explode('/',$url ,2);
			$ar = explode('?',$ar[1],2);
			$key = $arr[0];
		}
		if(strlen($key) < 4)
		{
			$short = App::config('short.'.$key);
			$url = empty($short)?'home.index':$short;
			$arr = explode('.', $url);
		}else{
			
		}
		
		return $arr;
	}
}