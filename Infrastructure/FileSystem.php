<?php 
namespace Zodream\Infrastructure;
/**
 * 文件操作
 *
 * @author Jason
 * @time 2015-12-1
 */

class FileSystem {
	
	/**
	 * 生成视图路径
	 * @param unknown $name
	 * @return string
	 */
	public static function view($name) {
		$dir = trim((!defined('THEME_DIR') || THEME_DIR === '/') ? VIEW_DIR : THEME_DIR, '/');
		if (!empty($dir)) {
			$dir .= '/';
		}
		$name = str_replace('.', '/', $name);
		$file = APP_DIR.'/UserInterface/'.APP_MODULE.'/'. $dir .$name;
		$file = str_replace( '//', '/', $file);
	
		return $file . Config::theme('ext');
	}
	
	/**
	 * 遍历文件夹
	 */
	public static function findDir($dir) {
		$files    = array();
		$dir_list = @scandir($dir);
		foreach($dir_list as $file) {
			if ( $file != ".." && $file != "." ) {
				if (is_dir($dir . $file)) {
					$files = array_merge($files, self::findDir($dir. $file. '/'));
				} else {
					$files[] = $dir.$file;
				}
			}
		}
		return $files;
	}
	
	public static function getFile($file) {
		if(file_exists($file)) {
			return $file;
		}
		$vendor = dirname(dirname(dirname(__FILE__)));
		$file   = '/'. ltrim($file, '/');
		if (file_exists($vendor.$file)) {
			return $vendor.$file;
		}
		$app = dirname(APP_DIR);
		if (file_exists($app.$file)) {
			return $app.$file;
		}
	}
	
	public static function read($file) {
		return file_get_contents(self::getFile($file));
	}
	
	public static function writ($file, $data) {
		file_put_contents($file, $data);
	}
	
	/*
	 * PHP下载断点续传
	 */
	function dl_file_resume($file){
		 
		//检测文件是否存在
		if (!is_file($file)) { die("<b>404 File not found!</b>"); }
	
	
		$len = filesize($file);//获取文件大小
		$filename = basename($file);//获取文件名字
		$file_extension = strtolower(substr(strrchr($filename,"."),1));//获取文件扩展名
	
		//根据扩展名 指出输出浏览器格式
		switch( $file_extension ) {
			case "exe": $ctype="application/octet-stream"; break;
			case "zip": $ctype="application/zip"; break;
			case "mp3": $ctype="audio/mpeg"; break;
			case "mpg":$ctype="video/mpeg"; break;
			case "avi": $ctype="video/x-msvideo"; break;
			default: $ctype="application/force-download";
		}
	
		//Begin writing headers
		header("Cache-Control:");
		header("Cache-Control: public");
	
		//设置输出浏览器格式
		header("Content-Type: $ctype");
		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {//如果是IE浏览器
			# workaround for IE filename bug with multiple periods / multiple dots in filename
			# that adds square brackets to filename - eg. setup.abc.exe becomes setup[1].abc.exe
			$iefilename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
			header("Content-Disposition: attachment; filename=\"$iefilename\"");
		} else {
			header("Content-Disposition: attachment; filename=\"$filename\"");
		}
		header("Accept-Ranges: bytes");
	
		$size=filesize($file);
		//如果有$_SERVER['HTTP_RANGE']参数
		if(isset($_SERVER['HTTP_RANGE'])) {
			/*   ---------------------------
			 Range头域 　　Range头域可以请求实体的一个或者多个子范围。例如， 　　表示头500个字节：bytes=0-499 　
	
			 　表示第二个500字节：bytes=500-999 　　表示最后500个字节：bytes=-500 　　表示500字节以后的范围：
	
			 　bytes=500- 　　第一个和最后一个字节：bytes=0-0,-1 　　同时指定几个范围：bytes=500-600,601-999 　　但是
	
			 　服务器可以忽略此请求头，如果无条件GET包含Range请求头，响应会以状态码206（PartialContent）返回而不是以
	
			 　200 （OK）。
			 　---------------------------*/
			 
	
			// 断点后再次连接 $_SERVER['HTTP_RANGE'] 的值 bytes=4390912-
	
			list($a, $range)=explode("=",$_SERVER['HTTP_RANGE']);
			//if yes, download missing part
			str_replace($range, "-", $range);//这句干什么的呢。。。。
			$size2=$size-1;//文件总字节数
			$new_length=$size2-$range;//获取下次下载的长度
			header("HTTP/1.1 206 Partial Content");
			header("Content-Length: $new_length");//输入总长
			header("Content-Range: bytes $range$size2/$size");
			//Content-Range: bytes 4908618-4988927/4988928   95%的时候
		} else {//第一次连接
			$size2=$size-1;
			header("Content-Range: bytes 0-$size2/$size"); //Content-Range: bytes 0-4988927/4988928
			header("Content-Length: ".$size);//输出总长
		}
		//打开文件
		$fp=fopen("$file","rb");
		//设置指针位置
		fseek($fp,$range);
		//虚幻输出
		while(!feof($fp)){
			//设置文件最长执行时间
			set_time_limit(0);
			print(fread($fp,1024*8));//输出文件
			flush();//输出缓冲
			ob_flush();
		}
		fclose($fp);
		exit;
	}
}