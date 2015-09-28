<?php
namespace App\Lib;	
/*****************************************************
*全局方法
*
*
*
********************************************************/
use App\Lib\Object\OArray;
use App\Lib\Helper\HUrl;
use App\Lib\Web\WRequest;
use App\Lib\Role\RComma;

defined("DEBUG") or define("DEBUG", false);
define('APP_URL', Base::config('app.host')); 
define('APP_API' , isset($_GET['api'])?TRUE:FALSE);    //是否是API模式

class Base{
	
	public static $request;
	
	public static function main()
	{
		set_error_handler(array('app','error'));         //自定义错误输出
		register_shutdown_function(array('app','out'));   //程序结束时输出
		//Lang::setLang();                                //加载语言包 
		
		self::$request = new WRequest();
		
		self::load();
	}
	/**
	* 获取配置文件
	*
	* @access globe
	*
	* @param string|null $key 要获取的配置名
	* @return array,
	*/
	public static function config( $key = null )
	{
		$configs=require(APP_DIR."/app/conf/config.php");
		if(!empty($key))
		{
			$arr = explode('.',$key);
			foreach ($arr as $value) {
				if(isset($configs[$value]))
				{
					$configs = $configs[$value];
				}else{
					$configs = '';	
					continue;
				}
			}
		}
		return $configs;
	}

	/**
	 * 判断权限是否符合
	 *
	 * @access globe
	 *
	 * @param int $role 权限编号
	 *
	 * @return string
	 */
	public static function role( $role )
	{
		if( Auth::guest() )
		{
			return empty($role);
		}else{
			return RComma::judge($role,Auth::user()->role()->roles);
		}
	}
	
	/**
	 * 产生完整的网址
	 *
	 * @access globe
	 *
	 * @param string $file 本站链接
	 * @param bool $echo 是否输出
	 *
	 * @return string
	 */
	public static function url($file = null,$echo = TRUE)
	{
		if($file === null)
		{
			$hurl = new HUrl();
			$file = request_uri();
		}
		
		$url = APP_URL.'/'.$file;
		
		$url = preg_replace('/([^:]\/)\/+/', '$1', $url);
		//$url = str_replace( ':/', '://', $url);
		
		if($echo)
		{
			echo $url;
		}else{
			return $url;	
		} 
	}	

	/**
	* 主要是加载 js、css 文件
	*
	* @access globe
	*
	*
	* @return null
	*/
	public static function jcs()
	{
		$list = new OArray();
		
		$files = $list->sort(func_get_args());
		
		foreach ($files as $file) {
			if(is_string($file) && !empty($file))
			{
				$result='';
				if(!strstr($file,'://'))
				{
					$arr=explode('.',$file);
					switch(end($arr))
					{
						case 'js':
							$result= '<script src="'.self::url('asset/js/'.$file,false).'"></script>';
							break;
						case 'css':
							$result= '<link rel="stylesheet" type="text/css" href="'.self::url('asset/css/'.$file,false).'"/>';
							break;
						default:
							$result= '<script src="'.self::url('asset/js/'.$file,false).'.js"></script>';
							break;
					}
					echo $result;
				}else{
					$arr=explode('.',$file);
					switch(end($arr))
						{
							case 'js':
								$result= '<script src="'.$file.'"></script>';
								break;
							case 'css':
								$result= '<link rel="stylesheet" type="text/css" href="'.$file.'"/>';
								break;
							default:
								$result= '<script src="'.$file.'"></script>';
								break;
						}
				}
				
				
			}else if(is_object($file)){
				$file();
			}
		}
		
		
		
	}
	
	/**
	* 判断是否存在并输出
	*
	*
	* @param string $name 要显示的
	* @param string|function $text 默认值.
	*/
	public static function ech($name,$text = '')
	{
		$result = isset(self::$data[$name])?self::$data[$name]:$text;
		if (is_object($text)) 
		{
			$text($result);
		}else
		{
			$oarray = new OArray();
			echo $oarray->tostring($result);
		}
	}
	
	/**
	* 判断是否存在并返回
	*
	*
	* @param string $name 要返回的
	* @param string|function $text 默认值.
	*/
	public static function ret($name , $text = '')
	{
		$result = isset(self::$data[$name])?self::$data[$name]:$text;
		if (is_object($text)) 
		{
			$text($result);
		}else
		{
			return $result;
		}
	}

	/**
	 * 操作session 设置和获取值
	 *
	 * @param string $keys 关键字 多层用‘.’ 分割
	 * @param string $value 要设置的值
	 * @return string
     */
	public static function session( $keys, $value = false , $life = '')
	{
		if(!isset($_SESSION))
		{
			session_save_path(APP_DIR.'/tmp');
			session_start();
		}
		
		if(empty($keys))
		{
			session_destroy();
			return;
		}
		
		if(is_bool($value))
		{
			$result = $_SESSION;
			$arr = explode('.',$keys);
			foreach ($arr as $value) {
				$result = isset($result[$value])?$result[$value]:'';
			}
			
			return $result;
		}else{
			$arr = explode('.',$keys);
			$str = '$_SESSION';
			foreach ($arr as $val) {
				$str.="['{$val}']";
			}
			$str.=' = $value;';
			eval($str);
		}
		
	}

	/**
	* 跳转页面
	*
	* @access globe
	*
	* @param string $url 要跳转的网址
	* @param int $time 停顿的时间
	* @param string $msg 显示的消息.
	* @param string $code 显示的代码标志.
	*/
	public static function redirect($url, $time=0, $msg='',$code = '') {
		//多行URL地址支持
		$url        = str_replace(array("\n", "\r"), '', $url);
		if (empty($msg))
			$msg    = "系统将在{$time}秒之后自动跳转到{$url}！";
		if (!headers_sent()) {
			// go
			if (0 === $time) {
				header('Location: ' . $url);
			} else {
				header("refresh:{$time};url={$url}");
				
			}
		} else {
			$str    = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			self::$data['meta'] = $str;
		}
		self::$data['code'] = $code;
		self::$data['error'] = $msg;
		self::extend('404');
		exit();
	}
	
	//要传的值
	public static $data;
	//额外的值
	public static $extra;
	/**
	* 包含文件
	*
	* @access globe
	*
	* @param string $names 路径加文件名
	* @param string|null $param 要传的额外的值
	* @param string|null $replace 额外值是否替换
	* @,
	*/
	public static function extend( $names ,$param = null,$replace = null)
	{
		if($replace == '+')
		{
			self::$extra[] = $param;
		}else{
			self::$extra = $param;
		}
		
		$configs = self::config('view');
				
		$view_dir = isset($configs['dir'])?$configs['dir']:'app/view';
		$ext = isset($configs['ext'])?$configs['ext']:'.php';
		
		if(substr( $ext , 0, 1 ) != '.')
		{
			$ext = '.'.$ext;
		}
		
		$list =new OArray();
		
		foreach ($list->to($names,'.') as $value) {
			self::inc_file($view_dir,$value,$ext);
		}
	}

	/**
	 * 加载视图文件
	 *
	 * @param string $view_dir 视图的路径
	 * @param string $name 视图文件名
	 * @param string $ext 视图文件的后缀
     */
	private static function inc_file($view_dir, $name ,$ext)
	{
		if(empty($name))
		{
			return;
		}
		$name = str_replace('.','/',$name);
		
		$file = APP_DIR.'/'.$view_dir.'/'.$name;
		
		$file = str_replace('//','/',$file);
		
		//extract(self::$data);
		require($file.$ext);
	}

	/**
	 * 加载控制器和视图
	 *
	 * @access globe
	 * @param $c string 控制器的名称
	 * @param $v string 视图所在的方法名
	 */
	private static function load()
	{
		$url = HUrl::get();
		
		$con = ucfirst(strtolower($url[0]));
		$name = 'App\\Controller\\'.$con."Controller";
		$view = strtolower($url[1]);
		if( class_exists($name))
		{
			$controller = new $name();
			
			$controller -> before($view);
			
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



	/**
	* 获取真实IP
	*
	* @access globe
	*
	* @return string IP,
	*/
	public static function getIp(){  
		$realip = '';  
		$unknown = 'unknown';  
		if (isset($_SERVER)){  
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)){  
				$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);  
				foreach($arr as $ip){  
					$ip = trim($ip);  
					if ($ip != 'unknown'){  
						$realip = $ip;  
						break;  
					}  
				}  
			}else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)){  
				$realip = $_SERVER['HTTP_CLIENT_IP'];  
			}else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)){  
				$realip = $_SERVER['REMOTE_ADDR'];  
			}else{  
				$realip = $unknown;  
			}  
		}else{  
			if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)){  
				$realip = getenv("HTTP_X_FORWARDED_FOR");  
			}else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)){  
				$realip = getenv("HTTP_CLIENT_IP");  
			}else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)){  
				$realip = getenv("REMOTE_ADDR");  
			}else{  
				$realip = $unknown;  
			}  
		}  
		$realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;  
		return $realip;  
	}

	/**
	 * @param array|string $info 调试的信息
     */
	public static function out($info=null)
	{
		if( defined('DEBUG') && DEBUG )
		{
			$error = error_get_last();

			if( !empty($error) || !empty($info))
			{
				if(!empty($error) || !empty($info))
				{
					header( 'Content-Type:text/html;charset=utf-8' );
					echo "<div style=\"text-align:center;color:red;font-weight:700;font-size:20px\">";
					empty($error)?'':printf("错误提示：%s！在%s中第%u行。",$error['message'],$error['file'],$error['line']);
					empty($info)?'':var_dump($info);
					echo '</div>';
				}
			}
		}
	}

	/**
	 * 调试时的输出错误信息
	 *
	 * @access globe
	 *
	 * @param int $errno 包含了错误的级别
	 * @param string $errstr 包含了错误的信息
	 * @param string $errfile  包含了发生错误的文件名
	 * @param int $errline 包含了错误发生的行号
	 * @param array $errcontext 是一个指向错误发生时活动符号表的 array
	 * @internal param array|null|string $info 信息
	 */
	public static function error($errno, $errstr, $errfile, $errline)
	{
		header( 'Content-Type:text/html;charset=utf-8' );
		if( defined('DEBUG') && DEBUG )
		{
			self::$data['error'] = '错误级别：'.$errno.'错误的信息：'.$errstr.'<br>发生在 '.$errfile.' 第 '.$errline.' 行！';
		}else{
			self::$data['error'] = '出错了！';
		}
		self::extend('404');
		die();
	}
	
	/**
	* 写日志记录
	*
	* @access globe
	*
	* @param string|array $logs 信息
	*/
	public static function writeLog($logs)
	{
		$log = '';
		if(is_array($logs))
		{
			foreach($logs as $k => $r){
				$log .= "{$k}='{$r}',";
			}
		}else{
			$log = $logs;
		}
		$logFile = date('Y-m-d').'.txt';
		$log = date('Y-m-d H:i:s').' >>> '.$log."\r\n";
		file_put_contents($logFile,$log, FILE_APPEND );
	}

}