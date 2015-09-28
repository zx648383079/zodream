<?php 
namespace App\Lib\Web;

class WRequest implements IBase 
{
	public $posts;
	
	public $gets;
	
	public $error = FALSE;
	
	public function __construct()
	{
		$this->gets = $_GET;
		$this->posts = $_POST;
	}
	
	public function get($name = null , $default = null)
	{
		if($name === null)
		{
			return $this->gets;
		}
		$arr = explode(',',$name);
		
		return $this->getVal($arr, $this->gets, $default);
	}
	
	public function post($name = null , $default = null)
	{
		if($name === null)
		{
			return $this->posts;
		}
		$arr = explode(',',$name);
		
		return $this->getVal($arr, $this->posts , $default);
	}
	
	public function delete()
	{
		
	}
	
	public function put()
	{
		
	}
	
	public function getMethod()
    {
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) 
		{
            return strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        } else {
            return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
        }
    }
	
	public function isGet()
    {
        return $this->getMethod() === 'GET';
    }

    public function isOptions()
    {
        return $this->getMethod() === 'OPTIONS';
    }

    public function isHead()
    {
        return $this->getMethod() === 'HEAD';
    }

    public function isPost()
    {
        return $this->getMethod() === 'POST';
    }

    public function isDelete()
    {
        return $this->getMethod() === 'DELETE';
    }

    public function isPut()
    {
        return $this->getMethod() === 'PUT';
    }

    public function isPatch()
    {
        return $this->getMethod() === 'PATCH';
    }

    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    public function isPjax()
    {
        return $this->isAjax() && !empty($_SERVER['HTTP_X_PJAX']);
    }

    public function isFlash()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) &&
            (stripos($_SERVER['HTTP_USER_AGENT'], 'Shockwave') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'Flash') !== false);
    }
	
	public function __get($name)
	{
		$arr = explode('_',$name);
		
		return $this->getVal($arr, array_merge($this->gets,$this->posts));
	}
	
	private function getVal($names, $values, $default = null)
	{
		$this->error = FALSE;
		$arr = array();
		
		foreach ($names as $name) 
		{
			//使用方法 post:key default
			
			$temp = explode(' ' , $name , 2 );
			$def = ( count($temp) == 1) ? $default : $temp[1];
			
			$temp = explode(':',$temp[0],2);
			$key = ( count($temp) == 1 ) ? $name : $temp[1];
			
			if(isset($values[$name]))
			{
				$arr[$key] = $values[$name];
			} else
			{
				$this->error[] = $name;
				$arr[$key] = $def;
			}
		}
		
		if(count($arr) == 1)
		{
			foreach ($arr as $value) {
				$arr = $value;
			}
		}
		
		return $arr;
	}
	
	private function safeCheck()
	{
		
	}
}