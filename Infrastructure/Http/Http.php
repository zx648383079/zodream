<?php 
namespace Zodream\Infrastructure\Http;
/**
* curl
* 
* @author Jason
* @time 2015-12-19
*/
class Http {
	private $curl;
	
	private $url;
	
	private $cookie;
	
	private $data;
	
	public function __construct() {
		
	}
	
	private function _init() {
		// 初始化一个cURL会话
		$this->curl = curl_init($this->url);
		// 不显示header信息
		curl_setopt($this->curl, CURLOPT_HEADER, 0);
		$this->_setCookie();
	}
	
	private function _common() {
		$this->_init();
		// 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		// 使用自动跳转
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
		// 自动设置Referer
		curl_setopt($this->curl, CURLOPT_AUTOREFERER, 1);
	}
	
	private function _close() {
		// 执行一个curl会话
		$data = curl_exec($this->curl);
		// 关闭curl会话
		curl_close($this->curl);
		return $data;
	}
	
	private function _bindValue() {
		$this->_common();
		// 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
		// 从证书中检查SSL加密算法是否存在
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 1);
		//模拟用户使用的浏览器，在HTTP请求中包含一个”user-agent”头的字符串。
		curl_setopt($this->curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		//发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
		curl_setopt($this->curl, CURLOPT_POST, 1);
		// 全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，
		// 在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串
		// 类似'para1=val1¶2=val2&...'或使用一个以字段名为键值，字段数据为值的数组
		// 如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->data);
	}
	
	public function get($url) {
		if (!empty($url)) {
			$this->url = $url;
		}
		$this->_common();
		return $this->_close();
	}
	
	public function post($url, $data) {
		if (!is_string($data)) {
			$data = http_build_query($data);
		}
		$this->url  = $url;
		$this->data = $data;
		$this->_bindValue();
		return $this->_close();
	}
	
	public function file($url, $data) {
		if (is_string($data)) {
			$data = array(
				'file' => '@'.realpath($data) //.';type='.$type.';filename='.$filename
			);
		}
		$this->url  = $url;
		$this->data = $data;
		$this->_bindValue();
		return $this->_close();
	}
	
	public function download($url, $file) {
		$this->url = $url;
		$this->_init();
		$fp = fopen($file, 'w');
		curl_setopt($this->curl, CURLOPT_FILE, $fp);
		$this->_close();
		fclose($fp);
	}
	
	private function _setCookie() {
		if (empty($this->cookie)) {
			return;
		}
		if (true) {
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, 'cookie.txt '); //保存 
			curl_setopt($this->curl, CURLOPT_COOKIEFILE, 'cookie.txt '); //读取  
		} else {
			$header[] = 'Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, text/html, * '. '/* '; 
			$header[] = 'Accept-Language: zh-cn '; 
			$header[] = 'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727) '; 
			$header[] = 'Host: '. parse_url($this->url)['host']; 
			$header[] = 'Connection: Keep-Alive '; 
			$header[] = 'Cookie: '. $this->cookie; 
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
		}
	}
}