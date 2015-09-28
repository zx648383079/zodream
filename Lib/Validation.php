<?php 
/* 
*
* 数据验证类，主要是表单验证
*
*
*/
namespace App\Lib;

use App\Lib\Db\DPdo;
	
class Validation
{
	
	public $error = array();
	
	private $request;

	/**
	 * 开始验证
	 *
	 * @param $request 要验证的数组
	 * @param $pattent 规则数组
	 * @return bool
     */
	public function make($request,$pattent)
	{
		$success = true;
		
		$this->request = $request;
		
		foreach($pattent as $key => $val)
		{
			$arr=explode('|',$val);
			
			if(isset($request[$key]) && !empty($request[$key]))
			{
				foreach($arr as $v)
				{
					$result = $this -> check($key, $v );
					
				 	if(!is_bool($result))
					{
						$this->error[$key][] = $key.$result;
						$success = false;
					}
				}
			}else{
				if(in_array('required',$arr))
				{
					$this->error[$key][] = $key.' is required';
					$success = false;
				}
			}
		}
		
		return $success;
	}

	/**
	 * 验证
	 *
	 * @param $key 关键字
	 * @param $patten 规则名
	 * @return bool|string
     */
	private function check($key, $patten)
	{
		$value = $this->request[$key];
		$result = FALSE;
		$arr = explode(':' , $patten , 2);
		switch(strtolower( $arr[0] ))
		{
			case 'required':
				$result = true;
				break;
			case 'number':
				$result = $this->isNum($value)?TRUE:' is not number';
				break;
			case 'email':
				$result = $this->isEmail($value)?TRUE:' is not email';
				break;
			case 'phone':
				$result = $this->isMobile($value)?TRUE:' is not phone';
				break;
			case 'url':
				$result = $this->isUrl($value)?TRUE:' is not url';
				break;
			case 'length':
				$len = explode('-',$arr[1]);
				$result = $this->length($value,3,intval($len[0]),intval($len[1]))?TRUE:'\'s length is not between '.$len[0].' and '.$len[1];
				break;
			case 'min':
				$result = $this->length($value,1,intval($arr[1]))?TRUE:' min length is '.$arr[1];
				break;
			case 'max':
				$result = $this->length($value,2,0,intval($arr[1]))?TRUE:' max length is '.$arr[1];
				break;
			case 'regular':
				$result = $this->regular($value,$arr[1])?TRUE:' is not match';
				break;
			case 'confirm':
				$result = $this->confirm($value , $arr[1])?TRUE:' is not the same as '.$arr[1];
				break;
			case 'conform':
				$result = ($value === $arr[1])?TRUE:' is not equal '.$arr[1];
				break;
			case 'unique':
				$tables = explode('.' , $arr[1],2);
				$colum = $key;
				if(!empty($tables[1]))
				{
					$colum =$tables[1];
				}
				$result =$this->unique($tables[0],$colum , $value)?TRUE:' is exist.';
				break;
			default:
				$result = TRUE;
				break;
		}
		
		return $result;
	}
	
	/**
	 * 对比确认
	 *
	 * @param string $table  不带前缀的表名
	 * @param string $value  要验证的列
	 * @param string $value  要验证的值
	 * @return bool
	 */
	public function unique($table , $colum ,$value)
	{
		$pdo =new DPdo();
		$data = $pdo->findByHelper(array(
			'select' => 'COUNT(*) as num',
			'from' => $table,
			'where' => "$colum = '$value'" 
		),false);
		if(empty($data) || $data[0]->num != '0')
		{
			return false;
		}else{
			return true;
		}
	}
	
	/**
	 * 对比确认
	 *
	 * @param string $value  要验证的值
	 * @param string $key 对比值得关键字
	 * @return bool
	 */
	private function confirm( $value, $key)
	{
		if(!isset($this->request[$key]))
		{
			return false;
		}
		
		return ($value === $this->request[$key]);
		
	}

	/**
	 * 数字验证
	 *
	 * @param $str
	 * @param string $flag int是否是整数，float是否是浮点型
	 * @return bool
	 */
    private function isNum($str,$flag = 'int'){
        if(strtolower($flag) == 'int'){
            return ((string)(int)$str === (string)$str) ? true : false;
        }else{
            return ((string)(float)$str === (string)$str) ? true : false;
        }
    }

	/**
	 * 邮箱验证
	 * @param $str
	 * @return bool
	 */
    private function isEmail($str){
        return preg_match("/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i",$str) ? true : false;
    }
    //手机号码验证
	/**
	 * @param $str
	 * @return bool
     */
	private function isMobile($str){
        $exp = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#';
        if(preg_match($exp,$str)){
            return true;
        }else{
            return false;
        }
    }

	/**
	 * URL验证，纯网址格式，不支持IP验证
	 * @param $str
	 * @return bool
	 */
    private function isUrl($str){
        return preg_match('#(http|https|ftp|ftps)://([w-]+.)+[w-]+(/[w-./?%&=]*)?#i',$str) ? true : false;
    }

	/**
	 * 验证长度
	 * @param string $str
	 * @param int $type(方式，默认min <= $str <= max)
	 * @param int $min,最小值;
	 * @param int $max,最大值;
	 * @param string $charset 字符
	 * @return bool
	 */
    private function length($str, $type = 3, $min = 0 ,$max = 0, $charset = 'utf-8'){
        $len = mb_strlen($str,$charset);
        switch($type){
            case 1: //只匹配最小值
                return ($len >= $min) ? true : false;
                break;
            case 2: //只匹配最大值
                return ($max >= $len) ? true : false;
                break;
            default: //min <= $str <= max
                return (($min <= $len) && ($len <= $max)) ? true : false;
        }
    }

	/**
	 * 正则验证
	 * @param: string $str
	 * @param: string $patten 正则字符串
	 * @return bool
	 */
	private function regular($str,$patten)
	{
        return preg_match($str,$patten)?TRUE:false;
	}
}