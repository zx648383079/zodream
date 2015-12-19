<?php 
namespace Zodream\Infrastructure;
/**
* 错误信息类
* 
* @author Jason
*/

class Error extends \ErrorException {
	public function output() {
		throw $this;
	}
}