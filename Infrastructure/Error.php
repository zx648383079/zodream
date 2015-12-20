<?php 
namespace Zodream\Infrastructure;
/**
* 错误信息类
* 
* @author Jason
*/

class Error extends \Exception{
	protected $_message;
	protected $_file;
	protected $_line;
	
	public function __construct($message, $file = __FILE__, $line = __LINE__) {
		$this->_message = $message;
		$this->_file    = $file;
		$this->_line    = $line;
	}
	
	public function __toString() {
		return '错误信息：'.$this->_message.'! 发生在'.$this->_file.'第'.$this->_line.'行';
	}
	
	public function output() {
		throw $this;
	}
}