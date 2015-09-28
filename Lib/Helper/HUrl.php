<?php 
namespace App\Lib\Helper;

use App;

class HUrl implements IBase
{
	public static function get()
	{
		$key = App::config('app.url');
		if(empty($key))
		{
			$key = 0;
		}
		$url = new HUrl();
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
		$url = $this->request_uri();
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
		$url = $this->request_uri();
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
		$url = $this->request_uri();
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
			$url = $this->request_uri();
			$ar = explode('/',$url ,2);
			$ar = explode('?',$ar[1],2);
			$key = $arr[0];
		}
		if(strlen($key) < 4)
		{
			$short = Main::config('short.'.$key);
			$url = empty($short)?'home.index':$short;
			$arr = explode('.', $url);
		}else{
			
		}
		
		return $arr;
	}
	/**
	 * 获取网址
	 *
	 * @return string 真实显示的网址
     */
	public function request_uri()
	{
		$uri = '';
		if ( isset($_SERVER['REQUEST_URI'] ) )
		{
			$uri = $_SERVER['REQUEST_URI'];
		}
		else
		{
			if ( isset( $_SERVER['argv'] ) )
			{
				$uri = $_SERVER['REQUEST_URI'];
			}
			else
			{
				if (isset($_SERVER['argv']))
				{
					$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
				}
				else
				{
					$uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
				}
			}
		}
		return $uri;
	}
}