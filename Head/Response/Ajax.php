<?php
namespace Zodream\Head\Response;

use Zodream\Body\Request;
class Ajax {
	/**
	 * 返回JSON数据
	 *
	 * @param array|string $data 要传的值
	 * @param string $type 返回类型
	 */
	public function view($data, $type = 'JSON') {
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
				exit(Request::getInstance()->get('callback', 'jsonpReturn').'('.json_encode($data).');');
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
		foreach ($data as $key => $value) {
			if (is_numeric($key)) {
				$key = "unknownNode_". (string) $key;
			}
			$key = preg_replace('/[^a-z]/i', '', $key);
			if (is_array($value)) {
				$node = $xml->addChild($key);
				$this->xml_encode($value, $rootNodeName, $node);
			} else {
				$value = htmlentities($value);
				$xml->addChild($key, $value);
			}
	
		}
		return $xml->asXML();
	}
}