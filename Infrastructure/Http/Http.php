<?php 
namespace Zodream\Infrastructure\Http;
/**
* curl
* 
* @author Jason
* @time 2015-12-19
*/
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Request;

class Http {
	private $curl;
	
	private $url;
	
	private $cookie;
	
	private $data;
	
	public function __construct() {
		
	}

	/**
	 * 初始化
	 */
	private function _init() {
		// 初始化一个cURL会话
		$this->curl = curl_init($this->url);
		// 不显示header信息
		$this->setOpt(CURLOPT_HEADER, 0);
		$this->_setCookie();
	}

	/**
	 * 公共部分
	 */
	private function _common() {
		$this->_init();
		// 将 curl_exec()获取的信息以文件流的形式返回，而不是直接输出。
		$this->setOpt(CURLOPT_RETURNTRANSFER, 1);
		// 使用自动跳转
		$this->setOpt(CURLOPT_FOLLOWLOCATION, 1);
		// 自动设置Referrer
		$this->setOpt(CURLOPT_AUTOREFERER, 1);
	}

	public function setOpt($option, $value) {
		curl_setopt($this->curl, $option, $value);
		return $this;
	}

	/**
	 * 关闭并返回结果
	 * @return mixed
	 */
	private function _close() {
		// 执行一个curl会话
		$data = curl_exec($this->curl);
		// 关闭curl会话
		curl_close($this->curl);
		return $data;
	}

	public function checkSSL($check = false) {
		// 对认证证书来源的检查，0表示阻止对证书的合法性的检查。
		$this->setOpt(CURLOPT_SSL_VERIFYPEER, $check);
		// 从证书中检查SSL加密算法是否存在
		$this->setOpt(CURLOPT_SSL_VERIFYHOST, 1);
		return $this;
	}

	private function _bindValue() {
		//模拟用户使用的浏览器，在HTTP请求中包含一个”user-agent”头的字符串。
		$this->setOpt(CURLOPT_USERAGENT, Request::server('HTTP_USER_AGENT'));
		//发送一个常规的POST请求，类型为：application/x-www-form-urlencoded，就像表单提交的一样。
		$this->setOpt(CURLOPT_POST, 1);
		// 全部数据使用HTTP协议中的"POST"操作来发送。要发送文件，
		// 在文件名前面加上@前缀并使用完整路径。这个参数可以通过urlencoded后的字符串
		// 类似'para1=val1¶2=val2&...'或使用一个以字段名为键值，字段数据为值的数组
		// 如果value是一个数组，Content-Type头将会被设置成multipart/form-data。
		$this->setOpt(CURLOPT_POSTFIELDS, $this->data);
	}

	/**
	 * 设置网址
	 * @param string $url
	 * @param array|string $data
	 */
	public function setUrl($url, $data = array()) {
		if (empty($url)) {
			return $this;
		}
		if (!empty($data)) {
			$url = StringExpand::urlBindValue($url, $data);
		}
		$this->url = $url;
		$this->_common();
		return $this;
	}
	
	public function get($url, $data = array()) {
		$this->setUrl($url, $data);
		return $this->_close();
	}
	
	public function post($url, $data = array()) {
		if (!is_string($data)) {
			$data = http_build_query($data);
		}
		$this->data = $data;
		$this->setUrl($url);
		$this->_bindValue();
		return $this->_close();
	}

	public function put($url, $data = array()) {
		$this->setUrl($url, $data);
		$this->setopt(CURLOPT_CUSTOMREQUEST, 'PUT');
		return $this->_close();
	}
	public function patch($url, $data = array()) {
		$this->setUrl($url);
		$this->setopt(CURLOPT_CUSTOMREQUEST, 'PATCH');
		$this->setopt(CURLOPT_POSTFIELDS, $data);
		return $this->_close();
	}
	
	public function delete($url, $data = array()) {
		$this->setUrl($url, $data);
		$this->setopt(CURLOPT_CUSTOMREQUEST, 'DELETE');
		return $this->_close();
	}
	
	public function file($url, $data) {
		if (is_string($data)) {
			$data = array(
				'file' => '@'.realpath($data) //.';type='.$type.';filename='.$filename
			);
		}
		$this->data = $data;
		$this->setUrl($url);
		$this->_bindValue();
		return $this->_close();
	}
	
	public function download($url, $file) {
		$this->url = $url;
		$this->_init();
		$fp = fopen($file, 'w');
		$this->setOpt(CURLOPT_FILE, $fp);
		$this->_close();
		fclose($fp);
	}
	
	private function _setCookie() {
		if (empty($this->cookie)) {
			return;
		}
		if (true) {
			$this->setOpt(CURLOPT_COOKIEJAR, 'cookie.txt '); //保存 
			$this->setOpt(CURLOPT_COOKIEFILE, 'cookie.txt '); //读取  
		} else {
			$header[] = 'Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, text/html, * '. '/* '; 
			$header[] = 'Accept-Language: zh-cn '; 
			$header[] = 'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727) '; 
			$header[] = 'Host: '. parse_url($this->url)['host']; 
			$header[] = 'Connection: Keep-Alive '; 
			$header[] = 'Cookie: '. $this->cookie;
			$this->setOpt(CURLOPT_HTTPHEADER, $header);
		}
	}
}