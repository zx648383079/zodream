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
	protected $curl;
	
	protected $url;
	
	public $cookieFile;
	
	protected $data;

	protected $headers = [
		'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
		'Accept-Language' => 'zh-cn',
		'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
		'Host' => '',
		'Connection' => 'Keep-Alive',
		'Cookie' => ''
	];
	
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
		$this->setHeaders();
	}
	
	public function setHeader($key, $value = null) {
		if (is_array($key) && is_null($value)) {
			$this->headers = array_merge($this->headers, $key);
			return $this;
		}
		$this->headers[$key] = $value;
		return $this;
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
	 * @return $this
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

	protected function setHeaders() {
		if (!empty($this->cookieFile) && is_file($this->cookieFile)) {
			$this->setOpt(CURLOPT_COOKIEJAR, $this->cookieFile); //保存
			$this->setOpt(CURLOPT_COOKIEFILE, $this->cookieFile); //读取
			unset($this->headers['Cookie']);
		}
		$header = [];
		foreach ($this->headers as $key => $item) {
			if ($key === 'Host' && empty($item)) {
				$item = parse_url($this->url)['host'];
			}
			if (empty($item)) {
				continue;
			}
			if (is_array($item)) {
				$item = implode(',', $item);
			}
			$header[] = $key. ':'. $item;
		}
		$this->setOpt(CURLOPT_HTTPHEADER, $header);
	}
}