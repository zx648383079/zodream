<?php
namespace Zodream\Domain\Response;

use Zodream\Infrastructure\Request;
class ResponseResult {
	/**
	 * 准备 必须有结束
	 * @param string $type
	 * @param number $status
	 */
	public static function prepare($type = 'html', $status = 200) {
		if ((!defined('APP_GZIP') || APP_GZIP) && extension_loaded('zlib')) {
			if (!headers_sent() && strpos(Request::getInstance()->server('HTTP_ACCEPT_ENCODING', ''), 'gzip') !== FALSE) {
				ob_start('ob_gzhandler');
			} else {
				ob_start();
			}
		} else {
			ob_start();
		}
		self::sendHttpStatus($status);
		/*if (is_array($type)) {
			call_user_func_array('self::sendContentType', $type);
		} else {
			self::sendContentType($type);
		}*/
		call_user_func_array('self::sendContentType', (array)$type);
		ob_implicit_flush(FALSE);
	}
	
	/**
	 * 结束并释放
	 */
	public static function finish() {
		ob_end_flush();
		exit;
	}
	
	/**
	 * 释放指定内容
	 * @param unknown $result
	 * @param string $type
	 * @param number $status
	 */
	public static function make($result, $type = 'html', $status = 200) {
		self::prepare($type, $status);
		echo $result;
		self::finish();
	}
	
	/**
	 * 显示错误页面
	 * @param array $data
	 * @param number $stauts
	 */
	public static function sendError($data = '', $stauts = 404) {
		View::getInstance()->set(array(
				'error' => $data,
				'status' => $stauts,
				'title' => '出错了！'
		));
		view::getInstance()->showWithFile($stauts, $stauts);
	}
	
	public static function sendRedirect($url, $time = 0) {
		if (0 === $time) {
			header('Location: ' . $url);
		} else {
			header("Refresh:{$time};url={$url}");
		}
	}
	
	public static function sendXPoweredBy($name = 'PHP/5.6.12') {
		header('X-Powered-By:'.$name);
	}
	
	/**
	 * WEB服务器名称
	 * @param string $name
	 */
	public static function sendServer($name = 'Apache') {
		header('Server:'.$name);
	}
	
	public static function sendContentLanguage($language = 'zh-CN') {
		header('Content-language:'.$language);
	}
	
	/**
	 * md5校验值
	 * @param unknown $md5
	 */
	public static function sendContentMD5($md5) {
		header('Content-MD5:'.$md5);
	}
	
	/**
	 * 缓存控制
	 * @param string $option 默认禁止缓存
	 */
	public static function sendCacheControl($option = 'no-cache, no-store, max-age=0, must-revalidate') {
		header('Cache-Control:'.$option);
	}
	
	/**
	 * 实现特定指令
	 * @param unknown $option
	 */
	public static function sendPragma($option) {
		header('Pragma: '.$option);
	}
	
	/**
	 * 如果实体不可取，指定时间重试
	 * @param unknown $time
	 */
	public static function sendRetryAfter($time) {
		header('Retry-After: '.$time);
	}
	
	/**
	 * 原始服务器发出时间
	 * @param unknown $time
	 */
	public static function sendDate($time) {
		header('Date: '.gmdate('D, d M Y H:i:s', $time).' GMT');
	}
	
	/**
	 * 响应过期的时间
	 * @param unknown $time
	 */
	public static function sendExpires($time) {
		header('Expires: '.gmdate('D, d M Y H:i:s', $time).' GMT');
	}
	
	/**
	 * 最后修改时间
	 * @param unknown $time
	 */
	public static function sendLastModified($time) {
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $time).' GMT');
	}
	
	/**
	 * 大小
	 * @param unknown $length
	 */
	public static function sendContentLength($length) {
		header('Content-Length:'.$length);
	}
	
	/**
	 * 文件流的范围
	 * @param unknown $length
	 * @param string $type
	 */
	public static function sendContentRange($length, $type = 'bytes') {
		header('Content-Range: '.$type.' '.$length);
	}
	
	/**
	 * 下载文件是指定接受的单位
	 * @param string $type
	 */
	public static function sendAcceptRanges($type = 'bytes') {
		header('Accept-Ranges:'.$type);
	}
	
	/**
	 * 下载文件的文件名
	 * @param unknown $filename
	 */
	public static function sendContentDisposition($filename) {
		if (strstr(Request::getInstance()->server('HTTP_USER_AGENT'), 'MSIE')) {     //如果是IE浏览器
			$filename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
		}
		header('Content-Disposition: attachment; filename="'.$filename.'"');
	}
	
	/**
	 * 文件传输编码
	 * @param unknown $encoding
	 */
	public static function sendTransferEncoding($encoding = 'chunked') {
		header('Transfer-Encoding: '.$encoding);
	}
	
	/**
	 * 返回内容的MIME类型
	 * @param string $type
	 * @param string $option
	 */
	public static function sendContentType($type = 'html', $option = 'utf-8') {
		switch (strtolower($type)) {
			case 'html':
				header('Content-Type:text/html;charset='.$option);
				break;
			case 'atom':
				header('Content-type:application/atom+xml');
				break;
			case 'css':
				header('Content-type:text/css');
				break;
			case 'js':
				header('Content-type:text/javascript');
				break;
			case 'image':
				header('Content-Type:image/'.$option);
				break;
			case 'json':
				header('Content-type:application/json;charset='.$option);
				break;
			case 'pdf':
				header('Content-type:application/pdf');
				break;
			case 'rss':
				header('Content-Type:application/rss+xml; charset='.$option);
				break;
			case 'text':
				header('Content-type:text/plain');
				break;
			case 'xml':
				header('Content-type:text/xml;charset='.$option);
				break;
			case 'csv':
				header('Content-type:text/csv;');
				break;
			case 'flash':
				header('Content-Type: application/x-shockwave-flash');
				break;
			case 'exe':
				header('Content-type:application/octet-stream');
				break;
			case 'zip':
				header('Content-type:application/zip');
				break;
			case 'mp3':
				header('Content-type:audio/mpeg');
				break;
			case 'mpg':
				header('Content-type:video/mpeg');
				break;
			case 'avi':
				header('Content-type:video/x-msvideo');
				break;
			default:
				header('Content-type:'.$type);
				break;
		}
	}
	
	/**
	 * 发送Http状态信息
	 * @param number $status
	 */
	public static function sendHttpStatus($status = 200)
	{
		static $_status = [
				// Informational 1xx
				100 => 'Continue',
				101 => 'Switching Protocols',
				// Success 2xx
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				// Redirection 3xx
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Moved Temporarily ', // 1.1
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				// 306 is deprecated but reserved
				307 => 'Temporary Redirect',
				// Client Error 4xx
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Long',
				415 => 'Unsupported Media Type',
				416 => 'Requested Range Not Satisfiable',
				417 => 'Expectation Failed',
				// Server Error 5xx
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported',
				509 => 'Bandwidth Limit Exceeded',
		];
		if (isset($_status[$status])) {
			header('HTTP/1.1 ' . $status . ' ' . $_status[$status]);
			// 确保FastCGI模式下正常
			header('Status:' . $status . ' ' . $_status[$status]);
		}
	}
}