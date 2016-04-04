<?php
namespace Zodream\Domain\Response;

use Zodream\Infrastructure\Request;
class Ajax {
	/**
	 * 返回JSON数据
	 *
	 * @param array|string $data 要传的值
	 * @param string $type 返回类型
	 */
	public static function ajaxReturn($data, $type = 'json') {
		switch (strtolower($type)) {
			case 'json' :
				ResponseResult::make(json_encode($data), $type);
				break;
			case 'xml'  :
				// 返回xml格式数据
				ResponseResult::make(self::_xmlEncode($data), $type);
				break;
			case 'jsonp':
				// 返回JSON数据格式到客户端 包含状态信息
				ResponseResult::make(Request::get('callback', 'jsonpReturn').'('.json_encode($data).');', 'json');
				break;
			case 'eval' :
				// 返回可执行的js脚本
				ResponseResult::make($data);
				break;
		}
	}
	
	/**
	 * 数组转XML
	 *
	 * @param array $data 要转的数组
	 * @param string $rootNodeName
	 * @param null $xml
	 * @return mixed
	 */
	private static function _xmlEncode($data, $rootNodeName = 'data', $xml = null) {
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
				self::_xmlEncode($value, $rootNodeName, $node);
			} else {
				$value = htmlentities($value);
				$xml->addChild($key, $value);
			}
	
		}
		return $xml->asXML();
	}
}