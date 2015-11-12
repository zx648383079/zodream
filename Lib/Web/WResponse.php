<?php 
namespace App\Lib\Web;

use App\Lib\Object\OArray;
use App\Lib\Object\OBase;
use App\Lib\Html\HView;

final class WResponse extends OBase {
	private static $_instance;
	/**
	 * 单例模式
	 * @return \App\Lib\Web\WResponse
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * 获取额外的值 主要是js、css文件或脚本
	 * @return string
	 */
	public function getExtra() {
		return $this->get('_extra');
	}
	
	/**
	 * 在视图中包含其他视图的方法
	 * @param string|array $names 视图文件名
	 * @param string|array $param 传给视图的内容
	 * @param string $replace 是否替换
	 */
	public function extend($names, $param = null, $replace = TRUE) {
		if (!$replace) {
			$param = array_merge((array)$this->getExtra(), (array)$param);
		}
		$this->set('_extra', $param);
		foreach (OArray::to($names, '.') as $value) {
			include(HView::make($value));
		}
	}
	
	private $_components;
	
	/**
	 * 按部件加载视图
	 * @param string $name
	 * @param string $data
	 */
	public function component($name = 'index', $data = null) {
		extract($data);
		ob_start();
		include(HView::make($name));
		$this->_components .= ob_get_contents();
		ob_end_clean();
		return $this;
	}
	
	/**
	 * 结束并释放视图
	 */
	public function render() {
		$this->showGzip($this->_components);
	}
	
	/**
	 * 加载视图
	 *
	 * @param string $name 视图的文件名
	 * @param array $data 要传的数据
	 */
	public function show($name = "index", $data = null) {
		if (!empty($data)) {
			$this->set($data);
		}
		if (APP_API) {
			$this->ajaxJson($this->get());
		} else {
			ob_start();
			include(HView::make($name));
			$content = ob_get_contents();
			ob_end_clean();
			$this->showGzip($content);
		}
	}
	
	public function showGzip($content) {
		if (extension_loaded('zlib')) {
			if (!headers_sent() && isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
					strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) {
						ob_start('ob_gzhandler');
					} else {
						ob_start();
					}
		} else {
			ob_start();
		}
		header( 'Content-Type:text/html;charset=utf-8' );
		ob_implicit_flush(FALSE);
		echo $content;
		ob_end_flush();
		exit;
	}
	
	/**
	 * 返回JSON数据
	 *
	 * @param array|string $data 要传的值
	 * @param string $type 返回类型
	 */
	public function ajaxJson($data, $type = 'JSON')
	{
		switch (strtoupper($type)) {
			case 'JSON' :
				// 返回JSON数据格式到客户端 包含状态信息
				header('Content-Type:application/json; charset=utf-8');
				exit(json_encode($data));
			case 'XML'  :
				// 返回xml格式数据
				header('Content-Type:text/xml; charset=utf-8');
				exit($this->_xmlEncode($data));
			case 'JSONP':
				// 返回JSON数据格式到客户端 包含状态信息
				header('Content-Type:application/json; charset=utf-8');
				$handler = isset($_GET['callback']) ? $_GET['callback'] : 'jsonpReturn';
				exit($handler.'('.json_encode($data).');');
			case 'EVAL' :
				// 返回可执行的js脚本
				header('Content-Type:text/html; charset=utf-8');
				exit($data);
		}
	
		exit;
	}
	
	/**
	 * 数组转XML
	 *
	 * @param array $data 要转的数组
	 * @param string $rootNodeName
	 * @param null $xml
	 * @return mixed
	 */
	private function _xmlEncode($data, $rootNodeName = 'data', $xml = null) {
		if (ini_get('zend.ze1_compatibility_mode') == 1) {
			ini_set ('zend.ze1_compatibility_mode', 0);
		}
		if ($xml == null) {
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
		}
		foreach($data as $key => $value) {
			if (is_numeric($key)) {
				$key = "unknownNode_". (string) $key;
			}
			$key = preg_replace('/[^a-z]/i', '', $key);
			if (is_array($value)) {
				$node = $xml->addChild($key);
				$this->xml_encode($value, $rootNodeName, $node);
			} else {
				$value = htmlentities($value);
				$xml->addChild($key,$value);
			}
	
		}
		return $xml->asXML();
	}
	
	/**
	 * 显示图片
	 *
	 * @param $img
	 */
	public function showImg($img) {
		header('Content-type:image/png');
		imagepng($img);
		imagedestroy($img);
	}
}