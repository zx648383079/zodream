<?php
namespace Zodream\Domain\Response;

use Zodream\Infrastructure\Request;
class Download {
	public static function make($file) {
		if (!is_file($file)) {
			ResponseResult::sendError('FILE NOT FIND');
		}
		
		$length = filesize($file);//获取文件大小
		$filename = basename($file);//获取文件名字
		$file_extension = strtolower(substr(strrchr($filename,"."),1));//获取文件扩展名
		
		ResponseResult::sendCacheControl('public');
		//根据扩展名 指出输出浏览器格式
		switch( $file_extension ) {
			case 'exe':
			case 'zip': 
			case 'mp3':
			case 'mpg':
			case 'avi': 
				ResponseResult::sendContentType($file_extension); 
				break;
			default: 
				ResponseResult::sendContentType('application/force-download');
				break;
		}
		ResponseResult::sendContentDisposition($iefilename);
		ResponseResult::sendAcceptRanges();
		$range = Request::getInstance()->server('HTTP_RANGE');
		$value = 0;
		//如果有$_SERVER['HTTP_RANGE']参数
		if(null !== $range) {
			/*   ---------------------------
			 Range头域 　　Range头域可以请求实体的一个或者多个子范围。例如， 　　表示头500个字节：bytes=0-499 　
		
			 　表示第二个500字节：bytes=500-999 　　表示最后500个字节：bytes=-500 　　表示500字节以后的范围：
		
			 　bytes=500- 　　第一个和最后一个字节：bytes=0-0,-1 　　同时指定几个范围：bytes=500-600,601-999 　　但是
		
			 　服务器可以忽略此请求头，如果无条件GET包含Range请求头，响应会以状态码206（PartialContent）返回而不是以
		
			 　200 （OK）。
			 　---------------------------*/
			 
		
			// 断点后再次连接 $_SERVER['HTTP_RANGE'] 的值 bytes=4390912-
		
			list($tag, $value) = explode('=', $range);
			//if yes, download missing part
			str_replace($value, '-', $value);//这句干什么的呢。。。。
			$byteLength = $length - 1;//文件总字节数
			$newLength = $byteLength - $value;//获取下次下载的长度
			ResponseResult::sendHttpStatus(206);
			ResponseResult::sendContentLength($newLength);//输入总长
			ResponseResult::sendContentRange($value.$byteLength.'/'.$length);
			//Content-Range: bytes 4908618-4988927/4988928   95%的时候
		} else {//第一次连接
			$byteLength = $length - 1;
			ResponseResult::sendContentRange('0-'.$byteLength.'/'.$length);
			ResponseResult::sendContentLength($length);//输出总长
		}
		//打开文件
		$fp = fopen($file.'', 'rb');
		//设置指针位置
		fseek($fp, $value);
		//虚幻输出
		while(!feof($fp)){
			//设置文件最长执行时间
			set_time_limit(0);
			print(fread($fp, 1024*8));//输出文件
			flush();//输出缓冲
			ob_flush();
		}
		fclose($fp);
		exit;
	}
}