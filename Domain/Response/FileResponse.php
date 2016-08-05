<?php
namespace Zodream\Domain\Response;

use Zodream\Infrastructure\Disk\File;
use Zodream\Infrastructure\Error\Error;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Request;

class FileResponse extends BaseResponse {
	protected $speed = 512;   // 下载速度

	/**
	 * @var File
	 */
	protected $file;
	
	protected $fileName;

	/**
	 * FileResponse constructor.
	 * @param string|File $file
	 * @param string $fileName
	 * @param int $speed
	 */
	public function __construct($file, $fileName = null, $speed = 512) {
		$this->file = $file instanceof File ? $file : new File($file);
		$this->fileName = $fileName ?: $this->file->getName();
		$this->setSpeed($speed);
	}

	/**
	 * @throws \Zodream\Infrastructure\Error\Exception
	 */
	public function sendContent() {
		if (!$this->file->exist()) {
			Error::out('FILE NOT FIND', __FILE__, __LINE__);
		}
		
		$length = $this->file->size();//获取文件大小
		$fileExtension = $this->file->getExtension();//获取文件扩展名
		
		ResponseResult::sendCacheControl('public');
		//根据扩展名 指出输出浏览器格式
		switch($fileExtension) {
			case 'exe':
			case 'zip': 
			case 'mp3':
			case 'mpg':
			case 'avi': 
				ResponseResult::sendContentType($fileExtension); 
				break;
			default: 
				ResponseResult::sendContentType('application/force-download');
				break;
		}
		ResponseResult::sendContentDisposition($this->fileName);
		ResponseResult::sendAcceptRanges();
		$range = self::getRange($length);
		//打开文件
		$fp = fopen($this->file->getFullName(), 'rb');
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
			ResponseResult::sendHttpStatus(206);
			ResponseResult::sendContentLength($range['end']-$range['start']);//输入剩余长度
			ResponseResult::sendContentRange(
				sprintf('%s-%s/%s', $range['start'], $range['end'], $length));
			//设置指针位置
			fseek($fp, sprintf('%u', $range['start']));
		} else {
			//第一次连接
			ResponseResult::sendHttpStatus(200);
			ResponseResult::sendContentLength($length);//输出总长
		}
		//虚幻输出
		while(!feof($fp)){
			//设置文件最长执行时间
			set_time_limit(0);
			print(fread($fp, round($this->speed * 1024, 0)));//输出文件
			flush();//输出缓冲
			ob_flush();
		}
		fclose($fp);
		exit;
	}

	/** 设置下载速度
	 * @param int $speed
	 */
	public function setSpeed($speed){
		if(is_numeric($speed) && $speed > 16 && $speed < 4096){
			$this->speed = $speed;
		}
	}

	/** 获取header range信息
	 * @param int $fileSize 文件大小
	 * @return array|null
	 */
	protected function getRange($fileSize){
		$range = Request::server('HTTP_RANGE');
		if (empty($range)) {
			return null;
		}
		$range = preg_replace('/[\s|,].*/', '', $range);
		$range = StringExpand::explode(substr($range, 6), '-', 2, array(0, $fileSize));
		return array(
			'start' => $range[0],
			'end' => $range[1]
		);
	}
}