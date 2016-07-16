<?php
namespace Zodream\Domain\Response;

use Zodream\Infrastructure\ObjectExpand\JsonExpand;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;
use Zodream\Infrastructure\Request;
class AjaxResponse extends BaseResponse {
	
	const JSON = 'json';
	
	const XML = 'xml';
	
	const JSONP = 'jsonp';
	
	const EVAL = 'eval';
	
	protected $data;
	protected $type;
	
	public function __construct($data, $type = self::JSON) {
		$this->data = $data;
		$this->type = strtolower($type);
	}

	/**
	 * 返回JSON数据
	 *
	 */
	public function sendContent() {
		switch ($this->type) {
			case self::JSON:
				ResponseResult::make(JsonExpand::encode($this->data), 'json');
				break;
			case self::XML:
				// 返回xml格式数据
				ResponseResult::make(XmlExpand::encode($this->data), 'xml');
				break;
			case self::JSONP:
				// 返回JSON数据格式到客户端 包含状态信息
				ResponseResult::make(
					Request::get('callback', 'jsonpReturn').'('.
					JsonExpand::encode($this->data).');', 
					'json'
				);
				break;
			case self::EVAL:
				// 返回可执行的js脚本
				ResponseResult::make($this->data);
				break;
		}
	}
}