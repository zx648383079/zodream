<?php 
namespace App\Lib\Object;

/**
*把数组或字符串转为单列数组
*/

class OArray implements IBase
{
	
	private $before = array();
	
	private $content = array();
	
	private $after = array();
	/**
	自定义排序 根据关键词 before after
	*/
	public function sort($arr)
	{
		$this->arr_list($arr);
		
		return array_merge($this->before, $this->content ,$this->after);
	}
	
	private function arr_list($arr)
	{
		foreach ($arr as $key => $value)
		{
			switch ($key) {
				case 'before':
					if(is_array($value))
					{
						$this->before = array_merge($this->before , $value);
					}else{
						$this->before[] = $value;
					}
					break;
				case 'after':
					if(is_array($value))
					{
						$this->after = array_merge($this->after , $value);
					}else{
						$this->after[] = $value;
					}
					break;
				default:
					if(is_array($value))
					{
						$this->arr_list($value);
					}else{
						$this->content[] = $value;
					}
					break;
			}
		}
	}
	
	/***
	合并前缀  把 key 作为前缀 例如 返回一个文件夹下的多个文件路径
	array('a'=>arrray(
	'b.txt',
	'c.txt'
	)) 
	
	**/
	public function to( $arr , $link = null,$pre = null)
	{
		$list = array();
		if(is_array($arr))
		{
				foreach ($arr as $key => $value) {
					if(is_int($key))
					{
						if(is_array($value))
						{
							$list = array_merge($list, $this->to($value,$link,$pre));
						}else{
							$list[] =$pre.$value;
						}
					}else{
						if(is_array($value))
						{
							$list = array_merge($list, $this->to($value,$link,$key.$link));
						}else{
							$list[] = $pre.$key.$link.$value;
						}
					}
				}
		}else{
			$list[] = $pre.$arr;
		}
		
		return $list;
	}
	
	/****
	把多维数组转换成字符串
	*******/
	public function tostring($arr ,$link  = '')
	{
		$str = '';
		if(is_array($arr))
		{
			foreach ($arr as $value) {
				$str .= $this->tostring($value , $link);
			}
		}else{
			$str .= $arr.$link;
		}
		return $str;
	}
}