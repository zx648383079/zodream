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
use App\Lib\Object\OString;

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
			if(method_exists( $controller, $view ) )
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
			App::$request->get('c' , 'home'),
			App::$request->get('v' , 'index')
		);
	}
	
	private function r()
	{
		$r = App::$request->get('r' , 'home/index');
		
		return OString::toArray($r, '/' ,2 , array( 'home', 'index' ));
	}
	
	private function u()
	{
		$url = HUrl::request_uri();
		$arr = OString::toArray($url, '.php' ,2 , array( '','/home/index' ));
		$arr = OString::toArray($arr[1], '/' ,4 , array( '' , 'home', 'index',''));
		
		return array($arr[1],$arr[2]);
	}
	
	private function y()
	{
		$url = HUrl::request_uri();
		$arr = OString::toArray($url , '/' , 4 , array( '' , 'home', 'index',''));
		
		return array($arr[1],$arr[2]);
	}
	
	private function p()
	{
		$url = HUrl::request_uri();
		preg_match($preg , $url , $result);
		return $result;
	}
	
	private function s()
	{
		$key = App::$request->get('s');
		if($key === null)
		{
			$url = HUrl::request_uri();
			$ar = explode('/',$url ,2);
			$ar = explode('?',$ar[1],2);
			$key = $ar[0];
			$key = $key == ''?'*':$key;
		}
		if(strlen($key) < 4)
		{
			$short = App::config('short.'.$key);
			$arr = OString::toArray( $short , '.' ,2 , array( 'home', 'index' )); 
		}else{
			
		}
		
		return $arr;
	}
}