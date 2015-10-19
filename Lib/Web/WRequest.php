<?php 
namespace App\Lib\Web;

use App\Lib\Object\OArray;

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
		
		return OArray::getVal($name, $this->gets, $default);
	}
	
	public function post($name = null , $default = null)
	{
		if($name === null)
		{
			return $this->posts;
		}
		
		
		return OArray::getVal($name, $this->posts , $default);
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
		return OArray::getVal($name, array_merge($this->gets,$this->posts), null, '_');
	}
	
	private function safeCheck()
	{
		
	}
}