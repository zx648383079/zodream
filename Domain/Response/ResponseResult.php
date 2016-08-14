<?php
namespace Zodream\Domain\Response;

use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Event\EventManger;
use Zodream\Infrastructure\Request;
class ResponseResult {
	/**
	 * 准备 必须有结束
	 * @param string $type
	 * @param int|number $status
	 */
	public static function prepare($type = 'html', $status = 200) {
		if ((!defined('DEBUG') || !DEBUG) &&(!defined('APP_GZIP') || APP_GZIP) && extension_loaded('zlib')) {
			if (!headers_sent() && strpos(Request::server('HTTP_ACCEPT_ENCODING', ''), 'gzip') !== FALSE) {
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
	 * @param string $result
	 * @param string $type
	 * @param int|string $status
	 */
	public static function make($result, $type = 'html', $status = 200) {
		EventManger::getInstance()->run('response', array($result, $type, $status));
		self::prepare($type, $status);
		echo $result;
		self::finish();
	}

	/**
	 * 显示错误页面
	 * @param array|string $data
	 * @param integer $status
	 * @param string $title
	 * @return BadResponse
	 */
	public static function sendError($data = '', $status = 404, $title = '出错了！') {
		if (!is_array($data)) {
			$data = array(
				'message' => $data
			);
		}
		$data['status'] = $status;
		$data['title'] = $title;
		if (defined('DEBUG') && DEBUG) {
			Factory::view()->set($data);
		}
		return new BadResponse(Factory::view()->render(Config::getValue('error', 404)), $status);
	}
	
	public static function sendRedirect($url, $time = 0) {
		if (empty($url)) {
			return;
		}
		if (empty($time)) {
			header('Location:' . $url);
		} else {
			header("Refresh:{$time};url={$url}");
		}
		exit(); //必须结束才会成功跳转
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
	 * @param string $md5
	 */
	public static function sendContentMD5($md5) {
		header('Content-MD5:'.$md5);
	}
	
	/**
	 * 缓存控制
	 * @param string $option 默认禁止缓存
	 */
	public static function sendCacheControl(
		$option = 'no-cache, no-store, max-age=0, must-revalidate') {
		header('Cache-Control:'.$option);
	}
	
	/**
	 * 实现特定指令
	 * @param string $option
	 */
	public static function sendPragma($option) {
		header('Pragma: '.$option);
	}
	
	/**
	 * 如果实体不可取，指定时间重试
	 * @param integer $time
	 */
	public static function sendRetryAfter($time) {
		header('Retry-After: '.$time);
	}
	
	/**
	 * 原始服务器发出时间
	 * @param integer $time
	 */
	public static function sendDate($time) {
		header('Date: '.gmdate('D, d M Y H:i:s', $time).' GMT');
	}
	
	/**
	 * 响应过期的时间
	 * @param integer $time
	 */
	public static function sendExpires($time) {
		header('Expires: '.gmdate('D, d M Y H:i:s', $time).' GMT');
	}
	
	/**
	 * 最后修改时间
	 * @param integer $time
	 */
	public static function sendLastModified($time) {
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $time).' GMT');
	}
	
	/**
	 * 大小
	 * @param int|string $length
	 */
	public static function sendContentLength($length) {
		header('Content-Length:'.$length);
	}
	
	/**
	 * 文件流的范围
	 * @param integer $length
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
	 * @param string $filename
	 */
	public static function sendContentDisposition($filename) {
		if (strpos(Request::server('HTTP_USER_AGENT'), 'MSIE') !== false) {     //如果是IE浏览器
			$filename = preg_replace('/\./', '%2e', $filename, substr_count($filename, '.') - 1);
		}
		header('Content-Disposition: attachment; filename="'.$filename.'"');
	}
	
	/**
	 * 文件传输编码
	 * @param string $encoding
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
		if (headers_sent()) {
			return;
		}
		$type = strtolower($type);
		if ($type == 'image' || $type == 'img') {
			header('Content-Type:image/'.$option);
			return;
		}
		static $args = array(
			'ai'    => 'application/postscript',
			'aif'   => 'audio/x-aiff',
			'aifc'  => 'audio/x-aiff',
			'aiff'  => 'audio/x-aiff',
			'atom'  => 'application/atom+xml',
			'avi'   => 'video/x-msvideo',
			'bin'   => 'application/macbinary',
			'bmp'   => 'image/bmp',
			'cpt'   => 'application/mac-compactpro',
			'css'   => 'text/css',
			'csv'   => 'text/x-comma-separated-values',
			'dcr'   => 'application/x-director',
			'dir'   => 'application/x-director',
			'doc'   => 'application/msword',
			'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dvi'   => 'application/x-dvi',
			'dxr'   => 'application/x-director',
			'eml'   => 'message/rfc822',
			'eps'   => 'application/postscript',
			'exe'   => 'application/octet-stream',
			'flv'   => 'video/x-flv',
			'flash' => 'application/x-shockwave-flash',
			'gif'   => 'image/gif',
			'gtar'  => 'application/x-gtar',
			'gz'    => 'application/x-gzip',
			'hqx'   => 'application/mac-binhex40',
			'htm'   => 'text/html',
			'html'  => 'text/html',
			'jpe'   => 'image/jpeg',
			'jpeg'  => 'image/jpeg',
			'jpg'   => 'image/jpeg',
			'js'    => 'application/x-javascript',
			'json'  => 'application/json',
			'log'   => 'text/plain',
			'mid'   => 'audio/midi',
			'midi'  => 'audio/midi',
			'mif'   => 'application/vnd.mif',
			'mov'   => 'video/quicktime',
			'movie' => 'video/x-sgi-movie',
			'mp2'   => 'audio/mpeg',
			'mp3'   => 'audio/mpeg',
			'mp4'   => 'video/mpeg',
			'mpe'   => 'video/mpeg',
			'mpeg'  => 'video/mpeg',
			'mpg'   => 'video/mpeg',
			'mpga'  => 'audio/mpeg',
			'oda'   => 'application/oda',
			'pdf'   => 'application/pdf',
			'php'   => 'application/x-httpd-php',
			'php3'  => 'application/x-httpd-php',
			'php4'  => 'application/x-httpd-php',
			'phps'  => 'application/x-httpd-php-source',
			'phtml' => 'application/x-httpd-php',
			'png'   => 'image/png',
			'ppt'   => 'application/powerpoint',
			'ps'    => 'application/postscript',
			'psd'   => 'application/x-photoshop',
			'qt'    => 'video/quicktime',
			'ra'    => 'audio/x-realaudio',
			'ram'   => 'audio/x-pn-realaudio',
			'rm'    => 'audio/x-pn-realaudio',
			'rpm'   => 'audio/x-pn-realaudio-plugin',
			'rss'   => 'application/rss+xml',
			'rtf'   => 'text/rtf',
			'rtx'   => 'text/richtext',
			'rv'    => 'video/vnd.rn-realvideo',
			'shtml' => 'text/html',
			'sit'   => 'application/x-stuffit',
			'smi'   => 'application/smil',
			'smil'  => 'application/smil',
			'swf'   => 'application/x-shockwave-flash',
			'tar'   => 'application/x-tar',
			'tgz'   => 'application/x-tar',
			'text'  => 'text/plain',
			'tif'   => 'image/tiff',
			'tiff'  => 'image/tiff',
			'txt'   => 'text/plain',
			'wav'   => 'audio/x-wav',
			'wbxml' => 'application/wbxml',
			'wmlc'  => 'application/wmlc',
			'word'  => 'application/msword',
			'xht'   => 'application/xhtml+xml',
			'xhtml' => 'application/xhtml+xml',
			'xl'    => 'application/excel',
			'xls'   => 'application/excel',
			'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xml'   => 'text/xml',
			'xsl'   => 'text/xml',
			'zip'   => 'application/x-zip'
		);
		if (!array_key_exists($type, $args)) {
			header('Content-type:'.$type);
			return;
		}
		$content = 'Content-Type:'.$args[$type];
		if (in_array($type, array('html', 'json', 'rss', 'xml'))) {
			$content .= ';charset='.$option;
		}
		header($content);
	}

	/**
	 * 发送Http状态信息
	 * @param int $status
	 */
	public static function sendHttpStatus($status = 200) {
		if (headers_sent()) {
			return;
		}
		static $_status = [
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',            // RFC2518
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',          // RFC4918
			208 => 'Already Reported',      // RFC5842
			226 => 'IM Used',               // RFC3229
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			307 => 'Temporary Redirect',
			308 => 'Permanent Redirect',    // RFC7238
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
			413 => 'Payload Too Large',
			414 => 'URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Range Not Satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot',                                               // RFC2324
			422 => 'Unprocessable Entity',                                        // RFC4918
			423 => 'Locked',                                                      // RFC4918
			424 => 'Failed Dependency',                                           // RFC4918
			425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
			426 => 'Upgrade Required',                                            // RFC2817
			428 => 'Precondition Required',                                       // RFC6585
			429 => 'Too Many Requests',                                           // RFC6585
			431 => 'Request Header Fields Too Large',                             // RFC6585
			451 => 'Unavailable For Legal Reasons',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
			507 => 'Insufficient Storage',                                        // RFC4918
			508 => 'Loop Detected',                                               // RFC5842
			510 => 'Not Extended',                                                // RFC2774
			511 => 'Network Authentication Required',                             // RFC6585
		];
		if (isset($_status[$status])) {
			header('HTTP/1.1 ' . $status . ' ' . $_status[$status]);
			// 确保FastCGI模式下正常
			header('Status:' . $status . ' ' . $_status[$status]);
		}
	}
}