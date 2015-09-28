<?php 
namespace App\Lib;
/*****************************************************
* 路由
*
*
*
********************************************************/

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
		$url = HUrl::get();
		
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
				self::error(0,$view,__FILE__,__LINE__);
			}
		}else{
			self::error(0,$name.$view,__FILE__,__LINE__);
		}
	}
}