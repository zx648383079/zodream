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
	public static function ajaxReturn($data, $type = 'JSON') {
		switch (strtoupper($type)) {
			case 'JSON' :
				// 返回JSON数据格式到客户端 包含状态信息
				header('Content-Type:application/json; charset=utf-8');
				exit(json_encode($data));
			case 'XML'  :
				// 返回xml格式数据
				header('Content-Type:text/xml; charset=utf-8');
				exit(self::_xmlEncode($data));
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
	
	// 发送Http状态信息
	public static function sendHttpStatus($status)
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